<?php

namespace App\Charts;

use App\Models\DataTables\DataTableInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class MeterReadings
 *
 * Plot field values (gal, gpm. kW, kWh, ccf, etc.) for a date range.
 */
class MeterReadings implements ChartInterface
{
    public $reporter;

    public $type = 'line';

    public $pv;

    public $title;

    /**
     * MeterReadings constructor.
     */
    public function __construct(DataTableInterface $model, string $pv, string $title = null)
    {
        $this->reporter = $model->reporter();
        $this->pv = $pv;
        if ($title) {
            $this->title = $title;
        } else {
            $this->title = $pv.'Readings';
        }
    }

    public function applyRequest(Request $request)
    {
        $this->setDateRange($request->input('start'), $request->input('end', null));
    }

    public function setDateRange($start, $end)
    {
        $this->reporter->beginning($start);
        if ($end) {
            $this->reporter->ending($end);
        } else {
            $this->reporter->defaultEnding();
        }
    }

    /**
     * Returns the collection of data points to be plotted.
     */
    public function chartData(): Collection
    {
        $result = $this->reporter->pvReadings($this->pv);
        $data = $this->reporter->canvasTimeSeries($result);

        return $data;
    }

    /**
     * Returns an array representation of chart settings and data.
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
            'data' => [
                [
                    'color' => '#B0D0B0',
                    'type' => $this->type,
                    'xValueType' => 'dateTime',
                    'dataPoints' => $this->chartData()->toArray(),
                ],
            ],
        ];
    }

    /**
     * Returns an JSON string representation of chart settings and data.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
