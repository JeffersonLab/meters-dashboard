<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:24 AM
 */

namespace App\Reports;



use App\Exports\ConsumptionReportExport;
use App\Models\DataTables\DateRangeTrait;
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
     * @var array
     */
    protected $pvOptions = array();


    public function __construct()
    {
        $this->initItems();
        $this->defaultDates();
    }

    /**
     * Initialize the items collection.
     * @return mixed
     */
    abstract function initItems();


    public function __get($var){
        switch($var){
            case 'begins_at' : return $this->begins_at;
            case 'ends_at' : return $this->ends_at;
            case 'pv'       : return $this->pv;
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
        switch ($filterName){
            case 'start' : $this->beginning($value); break;
            case 'end' : $this->ending($value); break;
            case 'pv'  : $this->pv = $value; break;
            case 'names' : $this->makeNameFilter($value);
        }
    }


    /**
     * Returns the view that should be used to render the report.
     *
     */
    public function view(){
        return view('reports.item')
            ->with('report', $this);
    }


    /**
     * The item names used to filter report output.
     * @return string
     */
    public function names(){
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
    public function description(){
        return $this->description;
    }

    /**
     * The type of item being reported (building, meter, etc.)
     * @return mixed
     */
    public function itemType()
    {
        return $this->itemType;
    }

    public function pvOptions(){
        return $this->pvOptions;
    }

    /**
     * Returns the data collection of the report.
     *
     * @return Collection
     */
    public function data()
    {
        if ($this->hasFilters()){
            return $this->filteredData();
        }
        return $this->allData();

    }

    /**
     * Returns the data for all items.
     *
     * @return Collection
     */
    public function allData(){
        $data = new Collection();
        foreach ($this->items as $item) {
            $data->push($this->makeDataItem($item));
        }
        return $data->sortBy(function ($item, $key) {
            return $item->label;
        });
    }


    /**
     * Returns the data for the filtered subset items.
     *
     * @return Collection
     */
    protected function filteredData(){
        $data = new Collection();
        foreach ($this->nameFilter as $name){
            $found = $this->findItemByName($name);
            if ($found){
                $data->push($this->makeDataItem($found, $name));
            }else{
                $data->push($this->makeDataPlaceholder($name));
            }
        }
        return $data;
    }


    /**
     * Parses the provided string into an array of names to be used for filtering
     * which items get reported.
     *
     * @param $string
     */
    protected function makeNameFilter($string){
        $this->nameFilter = array_filter(preg_split('/[,\r\n]+/', $string));
    }

    /**
     * Have filters been specified?
     * @return bool
     */
    protected function hasFilters(){
        return ! empty($this->nameFilter);
    }


    /**
     * Returns the first item from the collection with a matching name.
     *
     * The provided name is tested against the name_alias, epics_name, and name
     * fields of the item in that order.
     *
     * @param $name
     * @return mixed
     */
    protected function findItemByName($name){
        return $this->items->first(function ($value, $key) use ($name) {
            $key = trim($name, " \t\n\r\0\x0B," );
            return ($key == $value->getAttribute('name_alias')
                    || $key == $value->getAttribute('epics_name')
                    || $key == $value->getAttribute('name')
            );
        });
    }

    /**
     * Returns a record structure useful for outputting the report
     *   item:  The model (meter, building) being reported upon.
     *   label: The label to be used in the report
     *   first: The first (value at initial date-time)
     *   last:  The first (value at final date-time)
     *   url:   A URL to the itme's detail page
     *   consumed: The difference between last and first values
     *   isComplete: whether the data time span matches the requested time span
     *
     * @param $model
     * @param string $label -- specify non-standard label
     * @return object
     */
    protected function makeDataItem($model, $label = ''){
        $model->reporter()->beginning($this->begins_at);
        $model->reporter()->ending($this->ends_at);
        $dataItem = [
            'item' => $model,
            'label' => ($label ? $label : $model->getPresenter()->reportLabel()),
            'first' => $model->reporter()->firstData(),
            'last' => $model->reporter()->lastData(),
            'url' => $model->getPresenter()->url(),
        ];

        // We set the items below after initializeing $dataItem so that we won't have
        // to make the expensive firstData(), lastData() calls again by simply
        // using the local first and last values to compute consumption.
        $dataItem['consumed'] = $this->consumed($dataItem['first'], $dataItem['last']);
        $dataItem['isComplete'] = $this->isComplete($dataItem['first'], $dataItem['last']);
        return (object) $dataItem;
    }

    /**
     * Returns a mostly empty record with fields identical to those of makeDataItem().
     *
     * With the exception of label, all values are null.
     *
     * @param $label
     * @return object
     */
    protected function makeDataPlaceholder($label){
        $placeHolder = [
            'item' => null,
            'label' => $label,
            'first' => null,
            'last' => null,
            'url' => null,
            'consumed' => null,
            'isComplete' => false,
        ];
        return (object) $placeHolder;
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
     * Returns true if the dates of first and last match
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

    public function initialValue($item, $pv)
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
        return new ConsumptionReportExport($this);
    }


}
