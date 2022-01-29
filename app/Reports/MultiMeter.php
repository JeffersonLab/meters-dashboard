<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/23/18
 * Time: 11:34 AM
 */

namespace App\Reports;


use App\Charts\MultiMeter as MultiMeterChart;
use App\Exports\MultiMeterDataExport;
use App\Models\Meters\Meter;
use App\Models\Meters\VirtualMeter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class MultiMeter implements ReportInterface
{
    /**
     * @var VirtualMeter
     */
    protected $virtualMeter;

    /**
     * @var \App\Charts\MultiMeter
     */
    protected $chart;

    /**
     * @var string  (power, water, gas)
     */
    protected $meterType;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param Request $request
     * @throws \Exception
     */
    function applyRequest(Request $request)
    {
        $this->virtualMeter = new VirtualMeter();
        if ($request->has('model_id')){
            $physicalMeters = Meter::whereIn('id',$request->input('model_id'))->get();
            $this->virtualMeter->setMeters($physicalMeters);
        }
        $this->chart = new MultiMeterChart($this->virtualMeter);
        $this->chart->applyRequest($request);
        $this->initMeterType($request->input('meterType'));
    }

    /**
     * Initializes the report type (power, water, or gas).
     * The report type is based on the type of the underlying physical meter
     * type if possible.   In the absence of type information from a physical
     * meter, the type will be set to the default value.
     *
     * @param $meterType
     */
    function initMeterType($meterType){
        if ($this->virtualMeter->type()){
            $this->meterType = $this->virtualMeter->type();
        }
        $this->meterType = $meterType;
    }

    function meterType(){
        if (! $this->meterType && $this->virtualMeter->type()){
            $this->meterType = $this->virtualMeter->type();
        }
        return $this->meterType;
    }

    /**
     * Returns the view that should be used to render the report.
     *
     * @return View
     */
    public function view(){

        JavaScript::put([
            'currentApiUrl' => route('reports.chart_data'),
        ]);

        return view('reports.multimeter')
            ->with('report', $this);
    }

    function title()
    {
       if (! $this->title){
           $prefix = ucfirst($this->meterType()).' '.str_plural('Meter',count($this->virtualMeter->meterIds()));
           return $prefix .' '. $this->virtualMeter->name();
       }
       return $this->title;
    }

    function description()
    {
        return 'Multi Meter Chart';
    }

    function chart(){
        return $this->chart;
    }

    function meter(){
        return $this->virtualMeter;
    }

    public function pvOptions(){
        switch($this->meterType()){
            case 'power': return ['totkW' => 'kW', 'llVolt' => 'Volt'];
            case 'water' : return ['galPerMin'=>'GPM'];
            case 'gas' : return ['ccfPerMin'=>'CCFPM'];
        }
        return [];
    }

    public function meterOptions(){
        switch($this->meterType()){
            case 'power': $query = Meter::where('type','power'); break;
            case 'water' : $query = Meter::where('type','water'); break;
            case 'gas' : $query = Meter::where('type','gas'); break;
            default: return [];
        }
        return $query->orderBy('epics_name')->pluck('epics_name','id')->toArray();
    }

    public function beginsAt(){
        return $this->chart()->beginsAt();
    }

    public function endsAt(){
        return $this->chart()->endsAt();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function data(){
        if ($this->virtualMeter->hasMeters()) {
            return $this->virtualMeter->dataTable()
                ->join('meters', 'meter_id', '=', 'id')
                ->whereIn('id', $this->virtualMeter->meterIds())
                ->where('date', '>=', $this->beginsAt())
                ->where('date', '<=', $this->endsAt())
                ->orderBy('date')->get();
        }else{
            return new Collection();
        }
    }

    function hasExcel()
    {
       return true;
    }

    public function getExcelExport()
    {
        return new MultiMeterDataExport($this);
    }

}
