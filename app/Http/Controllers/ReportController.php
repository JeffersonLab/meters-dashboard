<?php

namespace App\Http\Controllers;

use App\Exports\ConsumptionReportExport;
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
    public function index() {
        return View::make('reports.index');
    }

    /**
     * Display a report
     *
     * @param string $name - the name of the report to return
     * @param Request $request
     * @return Application|Factory|\Illuminate\Contracts\View\View|Collection|\Illuminate\View\View
     */
    public function show($name, Request $request) {
        $report = ReportFactory::make($name, $request);
        $view = $report->view();

        if ($report->hasExcel()){
            $view->with('excelUrl', $this->excelUrl($name, $request));
        }

        // If the incoming request didn't provide begin and end date, we'll
        // add them to the request now to indicate to the client what dates
        // are being reported.
        if (! $request->has('begin')){
            $request->merge(['begin' => $report->beginsAt()]);
        }
        if (! $request->has('end')){
            $request->merge(['end' => $report->endsAt()]);
        }

        // Export data for javascript client-side
        JavaScript::put([
            'request' => $request->all(),
            'reportTitle' => $report->title(),
            'metersData' => $this->getMeterData($report),
            'buildingsData' => $this->buildingData(Building::all()),
        ]);

        // Return view with data for blade template.
        return $view->with('request', $request)->with('report', $report);
    }

    /**
     * Return meter data for the types of meters that are appropriate for the report type.
     * @param ReportInterface $report
     * @return Collection|void
     */
    protected function getMeterData(ReportInterface $report)
    {
        if (is_a($report, PowerConsumption::class)){
            return $this->meterData(Meter::where('type','power')->get());
        }
        if (is_a($report, WaterConsumption::class)){
            return $this->meterData(Meter::where('type','water')->get());
        }
        if (is_a($report, GasConsumption::class)){
            return $this->meterData(Meter::where('type','gas')->get());
        }
        $this->meterData(Meter::all());
    }

    /**
     * @param Collection $buildings
     * @return Collection
     */
    protected function buildingData(Collection $buildings)
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
     * @param string $name - the name of the report to return
     * @param Request $request
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function excel($name, Request $request) {

        $report = ReportFactory::make($name, $request);
        if ($report->hasExcel()){
            return Excel::download($report->getExcelExport(), 'report.xlsx');
        }
        abort(404, 'Spreadsheet output is not currently available.');

    }



    protected function excelUrl($name, Request $request){
        if ($request->getQueryString()){
            return route('reports.excel', $name).'?'.$request->getQueryString();
        }
        return route('reports.excel', $name);
    }

}
