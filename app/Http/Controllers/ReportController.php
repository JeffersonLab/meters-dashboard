<?php

namespace App\Http\Controllers;

use App\Exports\ConsumptionReportExport;
use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use App\Reports\ReportFactory;
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

        //TODO limit meters to relevant type for the report
        JavaScript::put([
            'reportTitle' => $report->title(),
            'metersData' => $this->meterData(Meter::all()),
            'buildingsData' => $this->buildingData(Building::all()),
        ]);

        return $view->with('request', $request);
    }

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
        });
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
