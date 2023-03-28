<?php

namespace App\Exports;

use App\Reports\MultiMeter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;

class MultiMeterDataExport implements FromCollection, WithMapping, WithHeadings, WithEvents, ShouldAutoSize, WithStrictNullComparison
{
    protected $report;

    /**
     * ConsumptionReportExport constructor.
     */
    public function __construct(MultiMeter $report)
    {
        $this->report = $report;
    }

//    protected function firstDatum($row){
//        if (isset($row->first)){
//            return $row->first->{$this->report->pv};
//        }
//        return null;
//    }
//
//
//    protected function lastDatum($row){
//        if (isset($row->last)){
//            return $row->last->{$this->report->pv};
//        }
//        return null;
//    }

    /**
     * Is the given row considered empty?
     */
    protected function isEmptyRow($row): bool
    {
        return empty($row);
    }

    /**
     * Generates content for the note column.
     *
     * @return string
     */
//    protected function note($row){
//        if ($this->isEmptyRow($row)){
//            return 'N/A';
//        }
//        if(! $row->isComplete){
//            return sprintf("Incomplete Data: %s to %s",
//                    date('Y-m-d H:i', strtotime($row->first->date)),
//                    date('Y-m-d H:i', strtotime($row->last->date)));
//        }
//        return '';
//    }

    /**
     * Transform row data.
     *
     * @param  mixed  $row
     */
    public function map($row): array
    {
        //dd($row);
        return [
            $row->date,
            $row->epics_name,
            $row->{$this->report->chart()->pv()},
        ];
    }

    /**
     * Custom column headings.
     */
    public function headings(): array
    {
        return [
            'Date',
            'Meter',
            $this->report->chart()->pv(),
        ];
    }

    /**
     * Returns the data collection used to make the spreadsheet.
     */
    public function collection(): Collection
    {
        return $this->report->data();
    }

    /**
     * Register handlers to do manipulation of the underlying spreadsheet at
     * different phases of the export cycle.
     *
     *
     * @see https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/
     * @see https://laraveldaily.com/laravel-excel-export-formatting-and-styling-cells/
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->sheet->append([$this->report->title()], 'A1');
                $cellRange = 'A1:C1'; // All headers
                $event->sheet->getDelegate()->mergeCells($cellRange);
            },

            AfterSheet::class => function (AfterSheet $event) {
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

//                foreach ($event->sheet->getColumnIterator('F') as $row) {
//                    foreach ($row->getCellIterator() as $cell) {
//                        if (str_contains($cell->getValue(), '://')) {
//                            $cell->setHyperlink(new Hyperlink($cell->getValue(), 'Read'));
//
//                            // Upd: Link styling added
//                            $event->sheet->getStyle($cell->getCoordinate())->applyFromArray([
//                                'font' => [
//                                    'color' => ['rgb' => '0000FF'],
//                                    'underline' => 'single'
//                                ]
//                            ]);
//                        }
//                    }
//                }
            },
        ];
    }
}
