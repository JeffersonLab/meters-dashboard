<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/22/18
 * Time: 10:11 AM
 */

namespace App\Models\Meters;

use App\Models\BaseModel;
use App\Models\DataTables\DataTableInterface;
use App\Models\DataTables\DataTableReporter;
use App\Models\DataTables\DataTableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class VirtualMeter
 *
 * A virtual meter is an amalgam of multiple physical meters whose data is combined for
 * reporting purposes.
 */
class VirtualMeter extends BaseModel implements DataTableInterface
{
    use DataTableTrait;

    public static $rules = [
        'name' => 'required | max:80',
        'description' => 'max:255',

    ];

    public $fillable = [
        'name',
        'description',
    ];

    /*
     * @var string
     */
    protected $type;

    /**
     * @var \Carbon\Carbon
     */
    protected $begins_at;

    /**
     * @var DataTableReporter
     */
    protected $reporter;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $meters;

    /**
     * Virtual Meter constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->dataTableFk = 'meter_id';
        $this->begins_at = Carbon::now()->startOfDay();
        $this->meters = new Collection;
        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'begins_at' => 'datetime',
        ];
    }

    /**
     * Returns the collection physical meters comprising the VirtualMeter
     */
    public function meters(): Collection
    {
        if ($this->meters->isEmpty()) {
            $this->setMeters($this->physicalMeters()->get());
        }

        return $this->meters;
    }

    /**
     * Answers whether the VirtualMeter has physicalMeters.
     */
    public function hasMeters(): bool
    {
        if ($this->meters && $this->meters->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * Eloquent Relation for retrieving/querying associated Physical Meters from database.
     */
    public function physicalMeters(): BelongsToMany
    {
        return $this->belongsToMany(Meter::class, 'virtual_meter_meters',
            'virtual_meter_id', 'meter_id');
    }

    /**
     * Sets type of the virtual meter based on the type attribute
     * of the first physical meter it contains.
     */
    public function setMeterType()
    {
        $types = $this->meters()->pluck('type')->unique();
        if ($types->isNotEmpty()) {
            $this->type = $types->first();
        } else {
            $this->type = null;
        }
    }

    /**
     * Returns a name for the Virtual Meter.
     *
     * If an explicit meter name is not set, a generic name is returned that
     * is comprised of the concatenate physical meter names.
     */
    public function name(): string
    {
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        }

        return implode(' + ', $this->meters()->pluck('epics_name')->toArray());
    }

    /**
     * Returns the VirtualMeter's type (gas, power, water) which is
     * based on the type of its underlying physical meters.
     *
     * @return mixed
     */
    public function type()
    {
        if (! $this->type) {
            $this->setMeterType();
        }

        return $this->type;
    }

    /**
     * Set the meters collection directly.
     *
     * This is an alternative to saving the virtual meter to the database in order to generate an id
     * and then storing that id along with the ids of the related meters in the virtual_meter_meters
     * table in order to then load them via belongsToMany() relation in the meters() method.
     *
     * This alternative can allow the creation of ephemeral virtual meters that don't get stored in the
     * database.  A use case would be aggregating a user-defined set of meters to produce a multi-meter
     * report or chart.
     */
    public function setMeters(Collection $meters)
    {
        $this->meters = $meters;
        $this->setMeterType();
    }

    /**
     * Returns the ids of the physical meters that comprise the virtual meter
     */
    public function meterIds(): array
    {
        return $this->meters()->pluck('id')->toArray();
    }

    public function reporter(): DataTableReporter
    {
        if (! $this->reporter) {
            $this->reporter = new DataTableReporter($this);
        }

        return $this->reporter;
    }

    /**
     * The name of the table where meter data points are stored.
     */
    public function tableName(): string
    {
        return 'virtual_meter_data_'.$this->id;
    }

    /**
     * Returns a Query Builder for the appropriate data table.
     */
    public function dataTable(): Builder
    {
        $builder = null;
        foreach ($this->meters() as $meter) {
            $builder = $builder ? $builder->union($meter->dataTable()) : $meter->dataTable();
        }

        return $builder;
    }

    /**
     * Answer whether the data table has any rows for the current object.
     */
    public function hasData(): bool
    {
        if ($this->dataTable()->whereIn($this->dataTableFk(), $this->meterIds())->limit(1)->first()) {
            return true;
        }

        return false;
    }

    public function fillDataTable()
    {
        //Noop - VirtualMeters don't insert data.
    }

    /**
     * Return the datetime of expected next data table row
     */
    public function nextDataDate()
    {
        $latest = $this->lastDataDate();
        if ($latest) {
            return date('Y-m-d H:i',
                strtotime($latest->date) + config('meters.data_interval', 900));
        } else {
            return $this->begins_at->format('Y-m-d H:00');
        }
    }

    /**
     * Return the datetime of most recent data table row
     */
    public function lastDataDate()
    {
        return $this->dataTable()
            ->whereIn($this->dataTableFk(), $this->meterIds())
            ->latest('date')->first();
    }

    public function dbFields(): array
    {
        return $this->pvFields();
    }

    public function pvFields(): array
    {
        // All meters are required to be of the same type and will therefore
        // have the same fields.
        return $this->meters()->first()->pvFields();
    }
}
