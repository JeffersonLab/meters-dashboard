<?php

namespace App\Exports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
class MultiMeterDataExport implements WithMultipleSheets
{
    use Exportable;

    protected Collection $meters;

    protected string $begin;

    protected string $end;

    /**
     * ConsumptionReportExport constructor.
     */
    public function __construct(Collection $meters, string $begin, string $end)
    {
        $this->meters = $meters;
        $this->begin = $begin;
        $this->end = $end;
    }

    public function sheets(): array {
        $sheets = [];
        foreach ($this->meters as $meter) {
            $sheets[] = new MeterDataExport($meter, $this->begin, $this->end);
        }
        return $sheets;
    }

}
