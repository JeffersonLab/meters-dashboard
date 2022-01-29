<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;



use App\Models\Buildings\Building;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class Consumption
 *
 * Report on resource consumption for a set of meters between two dates.
 *
 * @package App\Reports
 */
class GoalBuildings extends Consumption implements ReportInterface
{

    protected $title = 'Goal Buildings Report';
    protected $pv = 'totMBTU';  // default
    protected $data = null;
    protected $degreeDays = null;

    /*
     * Initialize report items.
     */
    public function initItems()
    {
        $this->items = Building::whereIn('name',$this->goalBuildingNames())->orderBy('name')->get();
        $this->itemType = 'building';
    }

    /*
     * Returns the names of the buildings to be included int the Goal Buildings Report.
     */
    public function goalBuildingNames(){
        return array_keys(config('reports.goal_buildings'));
    }

    /**
     * Returns the view that should be used to render the report.
     *
     */
    public function view(){
        return view('reports.goal_buildings')
            ->with('report', $this);
    }

    /**
     * Returns the data collection required for the report.
     *
     */
    public function data()
    {
        if ($this->data === null){
            $this->collectData();
        }
        return $this->data;
    }


    protected function collectData(){
        $this->data = new Collection();
        $degree_days = $this->degreeDays();
        foreach ($this->items as $item) {
            $item->reporter()->beginning($this->begins_at);
            $item->reporter()->ending($this->ends_at);
            $datum = [
                'item' => $item,
                'first' => $item->reporter()->firstData(),
                'last' => $item->reporter()->lastData(),
            ];
            //dd($item);
            $datum['mbtu'] = $this->consumed($datum['first'], $datum['last']);

            $datum['sqFt'] = $item->square_footage;
            $datum['mbtuPerSqFt'] = $this->mbtuPerSqFt($datum['mbtu'], $item->square_footage);
            $datum['mbtuPerDD'] = $this->mbtuPerDegreeDay($datum['mbtu'], $degree_days);
            $datum['mbtuPerSqFtPerDD'] = $this->mbtuPerSqFtPerDegreeDay(
                $datum['mbtu'], $item->square_footage, $degree_days
            );

            if ($datum['mbtu'] !== null) {
                // Bill Mooney has requested that the report actually display BTU
                // rather than MBTU as the per-sqft numbers are otherwise too miniscule.
                $datum['btu'] = $datum['mbtu'] * 1000000;
                $datum['btuPerSqFt'] = $datum['mbtuPerSqFt'] * 1000000;
                $datum['btuPerDD'] = $datum['mbtuPerDD']  * 1000000;
                $datum['btuPerSqFtPerDD'] = $datum['mbtuPerSqFtPerDD'] * 1000000;
                $this->data->push((object)$datum);
            }

        }
        return $this->data;
    }


    /**
     * Return an object with consumption totals for all buildings combined.
     *
     * @return object
     */
    public function totalConsumption(){
        $datum = array();
        $degree_days = $this->degreeDays();
        $sqFt = array_sum($this->data()->where('mbtu','>',0)->pluck('sqFt')->all());


        $datum['mbtu'] = array_sum($this->data()->pluck('mbtu')->all());
        $datum['sqFt'] = $sqFt;
        $datum['mbtuPerSqFt'] = $this->mbtuPerSqFt($datum['mbtu'], $sqFt);
        $datum['mbtuPerDD'] = $this->mbtuPerDegreeDay($datum['mbtu'], $degree_days);
        $datum['mbtuPerSqFtPerDD'] = $this->mbtuPerSqFtPerDegreeDay(
            $datum['mbtu'], $sqFt, $degree_days
        );

        $datum['btu'] = $datum['mbtu'] * 1000000;
        $datum['btuPerSqFt'] = $datum['mbtuPerSqFt'] * 1000000;
        $datum['btuPerDD'] = $datum['mbtuPerDD'] * 1000000;
        $datum['btuPerSqFtPerDD'] = $datum['mbtuPerSqFtPerDD'] * 1000000;

        return (object) $datum;
    }

    /**
     * Calculate MBTU per Square Foot.
     * Returns null if mbtu is null or square footage is zero.
     *
     * @param $mbtu
     * @param $sqFt
     * @return float|int|null
     */
    public function mbtuPerSqFt($mbtu, $sqFt){
        if ($mbtu !== null && $sqFt > 0) {
            return  $mbtu / $sqFt;
        }
        return null;
    }

    /**
     * Calculate MBTU per Degree Day.
     * Returns null if mbtu is null or degree days is zero.
     *
     * @param $mbtu
     * @param $degreeDays
     * @return float|int|null
     */
    public function mbtuPerDegreeDay($mbtu, $degreeDays){
        if ($mbtu !== null && $degreeDays > 0) {
            return  $mbtu / $degreeDays;
        }
        return null;
    }

    /**
     * Calculate MBTU per Square Foot per Degree Day.
     * Returns null if mbtu is null or square footage is zero or degree days is zero.
     *
     * @param $mbtu
     * @param $sqFt
     * @param $degreeDays
     * @return float|int|null
     */
    public function mbtuPerSqFtPerDegreeDay($mbtu, $sqFt, $degreeDays){
        $mbtuPerSqFt = $this->mbtuPerSqFt($mbtu, $sqFt);
        return $this->mbtuPerDegreeDay($mbtuPerSqFt, $degreeDays);
    }

    /**
     * Obtain the number of degree days in the report date range.
     *
     * @return mixed
     */
    public function degreeDays(){
        if ($this->degreeDays === null){
            $this->collectDegreeDays();
        }
        return $this->degreeDays;
    }

    protected function collectDegreeDays(){
        $query = DB::table('climate_data')
            ->select(\DB::raw('SUM(degree_days) AS degree_days'))
            ->whereBetween('date', [$this->begins_at, $this->ends_at])
            ->first();
        $this->degreeDays = $query->degree_days;
    }

}
