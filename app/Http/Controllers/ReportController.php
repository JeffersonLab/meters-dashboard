<?php

namespace App\Http\Controllers;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use App\Reports\GasConsumption;
use App\Reports\PowerConsumption;
use App\Reports\ReportFactory;
use App\Reports\ReportInterface;
use App\Reports\WaterConsumption;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Display the buildings index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\View\View
    {
        return View::make('reports.index');
    }

    /**
     * Display a report
     *
     * @param  string  $name - the name of the report to return
     * @return Application|Factory|\Illuminate\Contracts\View\View|Collection|\Illuminate\View\View
     */
    public function show(string $name, Request $request)
    {
        $report = ReportFactory::make($name, $request);
        $view = $report->view();

        if ($report->hasExcel()) {
            $view->with('excelUrl', $this->excelUrl($name, $request));
        }

        // Force the request timestamps we tell the client to match what the report uses.
        // NOTE: Always include time! If time is present then javascript will parse as local time
        // whereas it will parse date only in UTC.
        $request->merge(['begin' => $report->begins_at->format('Y-m-d H:i')]);
        $request->merge(['end' => $report->ends_at->format('Y-m-d H:i')]);

        // Export data for javascript client-side
        JavaScript::put([
            'request' => $request->all(),
            'reportTitle' => $report->title(),
            'metersData' => $this->getMeterData($report),
            'buildingsData' => $this->buildingData(Building::all()->sortBy('name', SORT_NATURAL)),
        ]);

        // Return view with data for blade template.
        return $view->with('request', $request)->with('report', $report);
    }

    /**
     * Return meter data for the types of meters that are appropriate for the report type.
     *
     * @return Collection|void
     */
    protected function getMeterData(ReportInterface $report)
    {
        if (is_a($report, PowerConsumption::class)) {
            return $this->meterData(Meter::where('type', 'power')->get());
        }
        if (is_a($report, WaterConsumption::class)) {
            return $this->meterData(Meter::where('type', 'water')->get());
        }
        if (is_a($report, GasConsumption::class)) {
            return $this->meterData(Meter::where('type', 'gas')->get());
        }
        $this->meterData(Meter::all());
    }

    /**
     * @return Collection
     */
    protected function buildingData(Collection $buildings): Collection
    {
        return $buildings->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'name' => $item->name,
                'epics_name' => $item->name,
                'building' => $item->name,
                'building_num' => $item->building_num,
            ];
        })->sortBy('building_num')->values();
    }

    /**
     * Output a report as an Excel spreadsheet
     *
     * @param  string  $name - the name of the report to return
     * @return BinaryFileResponse
     *
     * @throws \Exception
     */
    public function excel(string $name, Request $request): BinaryFileResponse
    {
        $report = ReportFactory::make($name, $request);
        if ($report->hasExcel()) {
            return Excel::download($report->getExcelExport(), 'report.xlsx');
        }
        abort(404, 'Spreadsheet output is not currently available.');
    }

    protected function excelUrl($name, Request $request)
    {
        if ($request->getQueryString()) {
            return route('reports.excel', $name).'?'.$request->getQueryString();
        }

        return route('reports.excel', $name);
    }
}
