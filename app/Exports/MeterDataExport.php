<?php

namespace App\Exports;

use App\Models\Meters\Meter;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MeterDataExport implements FromQuery, withHeadings, WithTitle {

    use Exportable;

    public Meter $meter;

    public function __construct( Meter $meter, string $begin, string $end) {
        $this->meter = $meter;
        $this->meter->reporter()->beginning($begin);
        $this->meter->reporter()->ending($end);
    }

    public function headings(): array {
        return array_merge(['meter'], $this->columns());
    }

    public function columns(){
        return array_merge(['date'], $this->meter->fields());
    }

    public function meterNameColumn() {
        return "'{$this->meter->epics_name}' as meter";
    }

    public function query() {
        return $this->meter->reporter()
            ->dateRangeQuery()
            ->select(array_merge([DB::raw($this->meterNameColumn())], $this->columns()));
    }

    public function title(): string {
        return $this->meter->epics_name;
    }

}
