<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;


use App\Exceptions\ReportingException;
use App\Exports\ConsumptionReportExport;
use App\Models\DataTables\DateRangeTrait;
use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class Consumption
 *
 * Used to report on resource consumption for a set of meters or buildings between two dates.
 *
 * @package App\Reports
 */
abstract class Consumption implements ReportInterface
{
    use DateRangeTrait;

    protected $title = 'Consumption';

    /**
     * Names to which the report output should be filtered/limited
     * @var array
     */
    protected $nameFilter = [];

    /**
     * @var Collection
     */
    protected $items;


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
     * @var array
     */
    protected $pvOptions = [];


    /**
     * @var Collection
     */
    protected $warnings;

    public function __construct()
    {
        $this->items = new Collection();
        $this->warnings = new Collection();
        $this->defaultDates();
        $this->setDayStartHour();
    }

    /**
     * Updates begins_at and ends_at properties to use a specific hour of the day for reporting.
     * For example to report on daily consumption from 8am - 8am as many utilities do rather than
     * midnight - midnight.
     * @param int $hour hour to use -- defaults to day_start_hour of reports config.
     * @return void
     */
    protected function setDayStartHour(int $hour = null){
        $hour = $hour ?: config('reports.day_start_hour');
        $this->begins_at->hour = $hour;
        $this->ends_at->hour = $hour;
    }

    public function __get($var)
    {
        switch ($var) {
            case 'begins_at' :
                return $this->begins_at;
            case 'ends_at' :
                return $this->ends_at;
            case 'pv'       :
                return $this->pv;
        }
        throw new \Exception('property not available');
    }


    /**
     * Apply filters from the provided HTTP request.
     *
     * @param Request $request
     * @return $this
     */
    public function applyRequest(Request $request)
    {
        foreach ($request->all() as $filterName => $value) {
            $this->applyNamedFilter($filterName, $value);
        }
        $this->updateItems();
        return $this;
    }


    /**
     * Uses the provided name and value to set up a report filter.
     *
     *
     * @param string $filterName
     * @param string $value
     */
    public function applyNamedFilter($filterName, $value)
    {
        switch ($filterName) {
            case 'begin' :
                $this->beginning($value);
                break;
            case 'end' :
                $this->ending($value);
                break;
            case 'pv'  :
                $this->pv = $value;
                break;
            case 'meters' :
                $this->makeNameFilter($value);
        }
    }

    /**
     * Chainable method to set the beginning of the reporting date range.
     * Overrides DateRangeTrait method of same name.
     * @param string $date
     * @return static
     */
    function beginning($date)
    {
        $this->begins_at = Carbon::parse($date);
        if (! $this->dateStringIncludesTime($date)){
              $this->begins_at->hour(config('reports.day_start_hour'));
        }
        return $this;
    }

    /**
     * Chainable method to set the beginning of the reporting date range.
     * Overrides DateRangeTrait method of same name.
     * @param string $date
     * @return static
     */
    function ending($date)
    {
        $this->ends_at = Carbon::parse($date);
        if (! $this->dateStringIncludesTime($date)){
            $this->ends_at->hour(config('reports.day_start_hour'));
        }
        return $this;
    }


    protected function dateStringIncludesTime(string $date){
        return preg_match('/^(\d\d\d\d-\d\d-\d\d)\s(\d\d:\d\d).*$/', $date);
    }

    /**
     * Update items property with fresh data from the database.
     * For example after applying updated filters.
     * @return void
     */
    protected function updateItems()
    {
        $this->items = Meter::whereIn('epics_name', $this->nameFilter)
            ->with('building')
            ->orderBy('epics_name')->get();
    }


    /**
     * Returns the view that should be used to render the report.
     *
     */
    public function view()
    {
        return view('reports.consumption')
            ->with('report', $this);
    }


    /**
     * The item names used to filter report output.
     * @return string
     */
    public function names()
    {
        return implode(",", $this->nameFilter);
    }

    /**
     * The report title.
     * @return string
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * The report description.
     *
     * @return string
     */
    public function description()
    {
        return $this->description;
    }


    public function pvOptions()
    {
        return $this->pvOptions;
    }

    /**
     * Returns the data collection of the report.
     *
     * @return Collection
     */
    public function data()
    {
        $data = new Collection();
            foreach ($this->items as $item) {
                try{
                    $data->push($this->makeDataItem($item));
                }catch (\Exception $e){
                    $this->warnings->push($item->name . ' ' . $e->getMessage());
                }
            }
        return $data;
    }

    public function warnings(){
        return $this->warnings;
    }

    /**
     * Return report data grouped by building.
     * @return Collection
     */
    public function dataByBuilding()
    {
        return $this->data()->sortBy('epics_name',SORT_NATURAL)->groupBy(function ($item, $key) {
            return $item->meter->building->building_num . ' ' . $item->meter->building->name;
        });
    }


    /**
     * Parses the provided string into an array of meter names and stores it in nameFilter property
     *
     * @param $string
     */
    protected function makeNameFilter($string)
    {
        $this->nameFilter = array_filter(preg_split('/[,\r\n]+/', $string));
    }

    /**
     * Have filters been specified?
     * @return bool
     */
    protected function hasFilters()
    {
        return !empty($this->nameFilter);
    }


    /**
     * Returns the first item from the collection with a matching name.
     *
     * The provided name is tested against the name_alias, epics_name, and name
     * fields of the item in that order.
     *
     * @param string $name
     * @return mixed
     */
    protected function findItemByName($name)
    {
        return $this->items->first(function ($value, $key) use ($name) {
            $key = trim($name, " \t\n\r\0\x0B,");
            return ($key == $value->getAttribute('name_alias')
                || $key == $value->getAttribute('epics_name')
                || $key == $value->getAttribute('name')
            );
        });
    }

    /**
     * Returns a record structure useful for outputting the report
     *   meter:  The meter being reported upon.
     *   label: The label to be used in the report
     *   first: The first (value at initial date-time)
     *   last:  The first (value at final date-time)
     *   url:   A URL to the item's detail page
     *   consumed: The difference between last and first values
     *   isComplete: whether the data time span matches the requested time span
     *
     * @param Meter $model
     * @return object
     */
    protected function makeDataItem($model)
    {
        $dataItem = [
            'meter' => $model,
            'label' => $model->epics_name,
            'first' => $model->firstDataBetween($this->pv, $this->begins_at, $this->ends_at),
            'last' => $model->lastDataBetween($this->pv, $this->begins_at, $this->ends_at),
            'url' => $model->getPresenter()->url(),
        ];

        // We set the items below after initializeing $dataItem so that we won't have
        // to make the expensive firstData(), lastData() calls again by simply
        // using the local first and last values to compute consumption.
        $dataItem['consumed'] = $this->consumed($dataItem['first'], $dataItem['last']);
        $dataItem['isComplete'] = $this->isComplete($dataItem['first'], $dataItem['last']);
        return (object)$dataItem;
    }


    /**
     * Calculates the quantity between first and last values after checking to ensure that
     * those values are actually set for the .  Returns null when either the first or last values
     *
     * @param $first
     * @param $last
     * @return float|null
     */
    public function consumed($first, $last)
    {
        if (isset($first->{$this->pv}) && isset($last->{$this->pv})) {
            return round($last->{$this->pv} - $first->{$this->pv}, 1);
        }
        return null;
    }

    /**
     * Returns true if the dates of first and last data values match
     * the dates of the report beginning and ending.
     * @param object $first {date}
     * @param object $last {date}
     * @return bool
     */
    public function isComplete($first, $last)
    {
        if (isset($first->date) && isset($last->date)) {
            $beginMatches = (strtotime($first->date) === $this->begins_at->timestamp);
            $endMatches = (strtotime($last->date) === $this->ends_at->timestamp);
            return $beginMatches && $endMatches;
        }
        return false;
    }

    /**
     * The initial value of the report PV at the beginning of the time interval.
     * @param Meter $item
     * @param string $pv
     * @return null
     */
    public function initialValue(Meter $item, $pv)
    {
        $data = $item->reporter()->firstData();
        if (isset($data->$pv)) {
            return $data->$pv;
        }
        return null;
    }


    /**
     * Is Excel (spreadsheet) output available for this report.
     * @return bool
     */
    public function hasExcel()
    {
        return true;
    }


    public function getExcelExport()
    {
        //TODO verify the excel export still works.
        return new ConsumptionReportExport($this);
    }


}
