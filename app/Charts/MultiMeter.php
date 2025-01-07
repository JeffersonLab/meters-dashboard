<?php

namespace App\Charts;

use App\Models\DataTables\DateRangeTrait;
use App\Models\Meters\VirtualMeter;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class MultiMeter
 *
 * Plot multiple meters in a single graph.
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
     *
     *
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
     * @return mixed|void
     */
    public function applyRequest(Request $request)
    {
        $this->setDateRange($request->input('start'), $request->input('end', null));
        $this->pv = $request->input('pv');
    }

    /**
     * Set the date range for data points to be plotted.
     *
     * @param  mixed  $start  string or Carbon datetime object (default: start of month)
     * @param  mixed  $end  string or Carbon datetime object  (default: now)
     */
    public function setDateRange($start, $end)
    {
        if ($start) {
            $this->beginning($start);
        } else {
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
     *
     * @throws \Exception
     */
    public function chartData(): array
    {
        $data = [];
        foreach ($this->virtualMeter->meters() as $meter) {
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
     *
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'title' => [
                'text' => $this->title,
            ],
            'axisY' => [
                'includeZero' => false,
            ],
            'legend' => [
                'cursor' => 'pointer',
                'verticalAlign' => 'top',
                'horizontalAlign' => 'center',
                'dockInsidePlotArea' => true,
            ],
            'data' => $this->chartData(),
        ];
    }

    /**
     * Returns an JSON string representation of chart settings and data.
     *
     *
     * @throws \Exception
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Returns the PV field being plotted.
     */
    public function pv(): string
    {
        return $this->pv;
    }

    /**
     * Returns the date at which the chart begins.
     */
    public function beginsAt(): Carbon
    {
        return $this->begins_at;
    }

    /**
     * Returns the date at which the chart ends.
     */
    public function endsAt(): Carbon
    {
        return $this->ends_at;
    }
}
