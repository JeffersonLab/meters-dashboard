<?php

namespace App\Reports;

use App\Exports\MultiMeterDataExport;
use App\Models\DataTables\DateRangeTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DataExport implements ReportInterface {

    use DateRangeTrait;

    use ReportFiltersTrait {
        // Alis the inherited method so we can extend it
        ReportFiltersTrait::applyRequest as public parentApplyRequest;
    }

    public $exportable;

    public function __construct()
    {
        $this->items = new Collection;
        $this->defaultDates();
        $this->setDayStartHour();
    }

    public function title() {
        return 'Data Export';
    }

    public function description() {
        return 'Exports meter data in a spreadsheet format';
    }


    /**
     * Chainable method to set the beginning of the reporting date range.
     * Overrides DateRangeTrait method of same name.
     *
     * @param  string  $date
     * @return static
     */
    public function beginning($date)
    {
        $this->begins_at = Carbon::parse($date);
        if (! $this->dateStringIncludesTime($date)) {
            $this->begins_at->hour(config('reports.day_start_hour'));
        }

        return $this;
    }

    /**
     * Chainable method to set the beginning of the reporting date range.
     * Overrides DateRangeTrait method of same name.
     *
     * @param  string  $date
     * @return static
     */
    public function ending($date)
    {
        $this->ends_at = Carbon::parse($date);
        if (! $this->dateStringIncludesTime($date)) {
            $this->ends_at->hour(config('reports.day_start_hour'));
        }

        return $this;
    }


    /**
     * Returns the view that should be used to render the report.
     */
    public function view()
    {
        return view('reports.data_export')
            ->with('report', $this);
    }

    public function hasExcel() {
        return true;
    }

    public function applyRequest(Request $request) {
        $this->parentApplyRequest($request);
        $this->makeExportable();
    }

    protected function makeExportable(){
        $this->exportable = new MultiMeterDataExport($this->items, $this->begins_at, $this->ends_at);
    }

    public function getExcelExport() {
        return $this->exportable->download('meter_data.xlsx');;
    }

    public function __get($var)
    {
        switch ($var) {
            case 'begins_at':
                return $this->begins_at;
            case 'ends_at':
                return $this->ends_at;
            case 'pv':
                return $this->pv;
        }
        throw new \Exception('property not available');
    }

}
