<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;

use App\Exports\MyaStatsDataExport;
use App\Meters\DateRangeTrait;
use App\Meters\Meter;
use App\Utilities\MySamplerStatsData;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

/**
 * Class MyaStats
 *
 * Used to report on meters using data from MyaStatsSampler
 */
class MyaStats extends MultiMeter
{
    use DateRangeTrait;

    protected $title = 'Stats';

    protected $stepSize = 86400;

    /**
     * Names to which the report output should be filtered/limited
     *
     * @var array
     */
    protected $nameFilter = [];

    /**
     * @var Collection
     */
    protected $items;

    /**
     * @var string
     */
    protected $itemType;

    /**
     * The process variable being report upon (ex: totkWh, gal, etc.)
     *
     * @var string
     */
    protected $pv;

    /**
     * An array indicicating which pvs are available for reporting.
     * The array is of the format ['pv' => 'label']
     * ex: ['gal'=>'Gallons'];
     *
     * @var array
     */
    protected $pvOptions = [];

    public function __construct()
    {
        $this->initItems();
        $this->defaultDates();
    }

    /**
     * Initialize the items collection.
     *
     * @return mixed
     */
    protected function initItems()
    {
        $this->items = new Collection();
    }

    public function __get($var)
    {
        switch ($var) {
            case 'begins_at': return $this->begins_at;
            case 'ends_at': return $this->ends_at;
            case 'pv': return $this->pv;
            case 'items': return $this->items;
        }
        throw new \Exception('property not available');
    }

    /**
     * Apply filters from the provided HTTP request.
     *
     * @return $this
     */
    public function applyRequest(Request $request): static
    {
        parent::applyRequest($request);

        foreach ($request->all() as $filterName => $value) {
            $this->applyNamedFilter($filterName, $value);
        }

        $this->fetchData();

        return $this;
    }

    protected function fetchData()
    {
        if (! empty($this->makePvs())) {
            $dataSource = new MySamplerStatsData($this->beginsAt(), $this->makePvs(), $this->stepSize);
            $this->items = $dataSource->getData();
        }
    }

    protected function makePvs()
    {
        $pvs = [];
        foreach ($this->virtualMeter->meters() as $meter) {
            $pvs[] = $meter->epics_name.':'.$this->pv;
        }

        return $pvs;
    }

    /**
     * Uses the provided name and value to set up a report filter.
     *
     *
     * @param  string  $filterName
     * @param  string  $value
     */
    public function applyNamedFilter(string $filterName, string $value)
    {
        switch ($filterName) {
            case 'start': $this->beginning($value);
            break;
            case 'end': $this->ending($value);
            break;
            case 'pv': $this->pv = $value;
            break;
            case 'names': $this->makeNameFilter($value);
        }
    }

    /**
     * Returns the view that should be used to render the report.
     *
     * @return Collection
     */
    public function view(): Collection
    {
        return view('reports.mya_stats')
            ->with('report', $this);
    }

    /**
     * The item names used to filter report output.
     *
     * @return string
     */
    public function names(): string
    {
        return implode(',', $this->nameFilter);
    }

    /**
     * The report title.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * The report description.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * The type of item being reported (building, meter, etc.)
     *
     * @return mixed
     */
    public function itemType()
    {
        return $this->itemType;
    }

    /**
     * Returns the data collection of the report.
     *
     * @return Collection
     */
    public function data(): Collection
    {
        return $this->allData();
    }

    /**
     * Returns the data for all items.
     *
     * @return Collection
     */
    public function allData(): Collection
    {
        $data = new Collection();
        foreach ($this->items as $item) {
            foreach ($this->makeDataItems($item) as $datum) {
                $data->push($datum);
            }
        }

        return $data->sortBy(function ($item, $key) {
            return $item->start;
        });
    }

    /**
     * Parses the provided string into an array of names to be used for filtering
     * which items get reported.
     */
    protected function makeNameFilter($string)
    {
        $this->nameFilter = array_filter(preg_split('/[,\r\n]+/', $string));
    }

    /**
     * Have filters been specified?
     *
     * @return bool
     */
    protected function hasFilters(): bool
    {
        return ! empty($this->nameFilter);
    }

    /**
     * Returns the first item from the collection with a matching name.
     *
     * The provided name is tested against the name_alias, epics_name, and name
     * fields of the item in that order.
     *
     * @return mixed
     */
    protected function findItemByName($name)
    {
        return $this->items->first(function ($value, $key) use ($name) {
            $key = trim($name, " \t\n\r\0\x0B,");

            return $key == $value->getAttribute('name_alias')
                    || $key == $value->getAttribute('epics_name')
                    || $key == $value->getAttribute('name');
        });
    }

    /**
     * Returns a record structure containing data for a single line
     * of report data table.
     *
     * @param  string  $label -- specify non-standard label
     * @return object
     */
    protected function makeDataItem($model, string $label = ''): object
    {
        // Not much to do right now but we expact to have to add
        // fields to the data item later.
        $model->label = $model->output->name;

        return $model;
    }

    /**
     * Returns a record structure containing data for a line of report data table.
     *
     * @param  string  $label -- specify non-standard label
     * @return array
     */
    protected function makeDataItems($model, string $label = ''): array
    {
        if (is_array($model->output)) {
            $items = [];
            foreach ($model->output as $output) {
                $item = new StdClass();
                $item->start = $model->start;
                $item->output = $output;
                $items[] = $this->makeDataItem($item);
            }
        } else {
            $items = [$this->makeDataItem($model)];
        }

        return $items;
    }

    /**
     * Returns a mostly empty record with fields identical to those of makeDataItem().
     *
     * With the exception of label, all values are null.
     *
     * @return object
     */
    protected function makeDataPlaceholder($label = 'N/A'): object
    {
        $placeHolder = [
            'start' => null,
            'label' => $label,
            'output' => [
                'mean' => null,
                'min' => null,
                'max' => null,
            ],
        ];

        return (object) $placeHolder;
    }

    /**
     * Calculates the quantity between first and last values after checking to ensure that
     * those values are actually set for the .  Returns null when either the first or last values
     *
     * @return float|null
     */
    public function consumed($first, $last): ?float
    {
        if (isset($first->{$this->pv}) && isset($last->{$this->pv})) {
            return round($last->{$this->pv} - $first->{$this->pv}, 1);
        }

        return null;
    }

    /**
     * Returns true if the dates of first and last match
     * the dates of the report beginning and ending.
     *
     * @param  object  $first {date}
     * @param  object  $last {date}
     * @return bool
     */
    public function isComplete(object $first, object $last): bool
    {
        if (isset($first->date) && isset($last->date)) {
            $beginMatches = (strtotime($first->date) === $this->begins_at->timestamp);
            $endMatches = (strtotime($last->date) === $this->ends_at->timestamp);

            return $beginMatches && $endMatches;
        }

        return false;
    }

    public function hasData()
    {
        return $this->items->isNotEmpty();
    }

    /**
     * Is Excel (spreadsheet) output available for this report.
     *
     * @return bool
     */
    public function hasExcel(): bool
    {
        return true;
    }

    public function getExcelExport()
    {
        return new MyaStatsDataExport($this);
    }
}
