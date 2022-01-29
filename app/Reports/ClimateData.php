<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/20/18
 * Time: 10:21 AM
 */

namespace App\Reports;


use App\Models\DataTables\DateRangeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClimateData implements ReportInterface
{

    /*
     * Note that using the DateRangeTrait gets us implementation of
     * ReportInterface's beginsAt() and endsAt() methods.
     */
    use DateRangeTrait;


    /**
     * @var string
     */
    protected $title = 'Climate Data';

    /**
     * ClimateData constructor.
     */
    public function __construct()
    {
        $this->defaultDates();
    }

    /**
     * Apply HTTP Request parameters to customize the report.
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
     * @return string
     */
    public function title(){
        return $this->title;
    }

    /**
     * @return string
     */
    public function description(){
        return '';
    }
    /**
     * Returns the view that should be used to render the report.
     *
     */
    public function view(){
        return view('reports.climate')
            ->with('report', $this);
    }


    /**
     * @param $filterName
     * @param $value
     */
    public function applyNamedFilter($filterName, $value)
    {
        switch ($filterName){
            case 'start' : $this->beginning($value); break;
            case 'end' : $this->ending($value); break;
        }
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function data(){
        return DB::table('climate_data')->select('*')
            ->where('date', '>=', $this->begins_at)
            ->where('date', '<=', $this->ends_at)
            ->orderBy('date')->get();
    }

    function hasExcel()
    {
        return false;
    }

    public function getExcelExport()
    {
        throw new \Exception('Climate Data Report has no Excel Export');
    }

}
