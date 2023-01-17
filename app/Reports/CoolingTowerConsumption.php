<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;



use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class PowerConsumption
 *
 * Report on power consumption for a set of meters between two dates.
 *
 * @package App\Reports
 */
class CoolingTowerConsumption extends Consumption
{

    protected $title = 'Cooling Tower Consumption';
    protected $description = 'This report details cooling-tower specific consumption accounting for evaporation losses';
    protected $pv = 'gal';

    public function __construct()
    {
        parent::__construct();
        $this->nameFilter = Building::where('type','CoolingTower')
        ->get()->pluck('name')->all();
    }


    /**
     * Update items property with fresh data from the database.
     * For example after applying updated filters.
     * @return void
     */
    protected function updateItems()
    {
        $this->items = Building::whereIn('name', $this->nameFilter)
            ->with('meters')->get()
            ->sortBy('name',SORT_NATURAL);
    }

    /**
     * Returns a record structure useful for outputting the report
     *   building:  The building being reported upon.
     *   label: The label to be used in the report
     *   consumption: sum of all supply meters
     *   sewer:  sum of all drain meters
     *   evaporation:   supply - drain
     *   concentration: supply / drain
     *   isComplete: whether the data time span matches the requested time span
     *
     * @param Meter $model
     * @return object
     */
    protected function makeDataItem($model)
    {
        $dataItem = [
            'building' => $model,
            'label' => $model->name,
            'consumption' => $model->waterConsumption($this->begins_at, $this->ends_at),
            'sewer' => $model->waterToSewer($this->begins_at, $this->ends_at),
            'evaporation' => $model->waterToEvaporation($this->begins_at, $this->ends_at),
            'concentration' => $model->waterCyclesOfConcentration($this->begins_at, $this->ends_at),
            'url' => $model->getPresenter()->url(),
        ];

        return (object)$dataItem;
    }


    /**
     * Returns the view that should be used to render the report.
     *
     */
    public function view()
    {
        return view('reports.cooling_tower_consumption')
            ->with('report', $this);
    }


}
