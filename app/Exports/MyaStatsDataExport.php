<?php

namespace App\Exports;

use App\Reports\Consumption as ConsumptionReport;
use App\Reports\MultiMeter;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;

class MyaStatsDataExport
    implements FromCollection, WithMapping, WithHeadings, WithEvents, ShouldAutoSize, WithStrictNullComparison
{

    protected $report;

    /**
     * ConsumptionReportExport constructor.
     * @param ConsumptionReport $report
     */
    public function __construct(MultiMeter $report)
    {
        $this->report = $report;
    }


    /**
     * Is the given row considered empty?
     *
     * @param $row
     * @return bool
     */
    protected function isEmptyRow($row){
        return empty($row);
    }



    /**
     * Transform row data.
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array {
        return [
            $row->start,
            $row->label,
            $row->output->mean,
            $row->output->min,
            $row->output->max,
        ];
    }

    /**
     * Custom column headings.
     * @return array
     */
    function headings(): array {
        return [
            'Date',
            'Signal',
            'Mean',
            'Min',
            'Max',
        ];
    }

    /**
     * Returns the data collection used to make the spreadsheet.
     *
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->report->data();
    }


    /**
     * Register handlers to do manipulation of the underlying spreadsheet at
     * different phases of the export cycle.
     *
     * @return array
     *
     * @see https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/
     * @see https://laraveldaily.com/laravel-excel-export-formatting-and-styling-cells/
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event){
                $event->sheet->append(['Stats'],'A1');
                $cellRange = 'A1:E1'; // All headers
                $event->sheet->getDelegate()->mergeCells($cellRange);

            },

            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:F2'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()
                    ->setSize(14)
                    ->setBold(true);

                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->getDelegate()->getStyle('E:F')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            },
        ];
    }
}
