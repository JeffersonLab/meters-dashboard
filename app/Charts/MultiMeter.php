<?php

namespace App\Charts;



use App\Models\DataTables\DateRangeTrait;
use App\Models\Meters\VirtualMeter;
use Illuminate\Http\Request;

/**
 * Class MultiMeter
 *
 * Plot multiple meters in a single graph.
 *
 * @package App\Charts
 */
class MultiMeter implements ChartInterface
{
    use DateRangeTrait;

    /**
     * The virtual meter that will hold the multiple physical meters
     * to be reported upon.
     *
     * @var VirtualMeter
     */
    protected $virtualMeter;

    /**
     * The canvasjs graph type being plotted.
     *
     * @var string
     */
    protected $type = 'line';

    /**
     * The PV field to be plotted.
     *
     * @var string (totkWh, gal, etc.)
     */
    protected $pv;

    /**
     * A title to be place in the chart.
     *
     * @var string
     */
    protected $title;


    /**
     * MultiMeter constructor.
     * @param VirtualMeter $model
     * @throws \Exception
     */
    public function __construct(VirtualMeter $model)
    {
        $this->virtualMeter = $model;
        $this->defaultDates();
        $this->title = $this->virtualMeter->name();
    }


    /**
     * Apply HTTP Request parameters to customize the chart.
     *
     * @param Request $request
     * @return mixed|void
     */
    function applyRequest(Request $request)
    {
        $this->setDateRange($request->input('start'), $request->input('end', null));
        $this->pv = $request->input('pv');
    }

    /**
     * Set the date range for data points to be plotted.
     *
     * @param mixed $start string or Carbon datetime object (default: start of month)
     * @param mixed $end string or Carbon datetime object  (default: now)
     */
    function setDateRange($start, $end)
    {
        if ($start){
            $this->beginning($start);
        }else{
            $this->defaultBeginning();
        }
        if ($end) {
            $this->ending($end);
        } else {
            $this->defaultEnding();
        }
    }

    /**
     * Returns the collection of data points to be plotted.
     *
     * @return array
     * @throws \Exception
     */
    public function chartData(){
        $data = array();
        foreach ($this->virtualMeter->meters() as $meter){
            //Each meter's datatable reporter needs to be told
            //of the current date range settings.
            $meter->reporter()->beginning($this->beginsAt());
            $meter->reporter()->ending($this->endsAt());

            $readings = $meter->reporter()->pvReadings($this->pv);
            $dataPoints = $meter->reporter()->canvasTimeSeries($readings);
            /** @noinspection PhpUndefinedMethodInspection */
            $data[] = [
                'showInLegend' => true,
                'type' => $this->type,
                'name' => $meter->epics_name,
                'xValueType' => 'dateTime',
                'dataPoints' => $dataPoints->toArray(),
                ];
        }
        return $data;
    }


    /**
     * Returns an array representation of chart settings and data.
     *
     * @return array
     * @throws \Exception
     */
    public function toArray(){
        return [
            'title' => [
                'text' => $this->title,
            ],
            'axisY' => [
                'includeZero' => false
            ],
            'legend' =>  [
                'cursor' => "pointer",
                'verticalAlign' => "top",
                'horizontalAlign' => "center",
                'dockInsidePlotArea' => true,
            ],
            'data' => $this->chartData(),
        ];
    }

    /**
     * Returns an JSON string representation of chart settings and data.
     *
     * @return string
     * @throws \Exception
     */
    public function toJson()
    {
        return json_encode($this->toArray());

    }

    /**
     * Returns the PV field being plotted.
     *
     * @return string
     */
    public function pv(){
        return $this->pv;
    }

    /**
     * Returns the date at which the chart begins.
     *
     * @return \Carbon\Carbon
     */
    public function beginsAt(){
        return $this->begins_at;
    }

    /**
     * Returns the date at which the chart ends.
     *
     * @return \Carbon\Carbon
     */
    public function endsAt(){
        return $this->ends_at;
    }

}
