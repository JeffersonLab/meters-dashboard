<?php

namespace App\Http\Controllers;

use App\Exports\ConsumptionReportExport;
use App\Reports\ReportFactory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use \View;



class ReportController extends Controller
{
    /**
     * Display the buildings index page
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return View::make('reports.index');
    }

    /**
     * Display a report
     *
     * @param string $name - the name of the report to return
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($name, Request $request) {
        $report = ReportFactory::make($name, $request);
        $view = $report->view();

        if ($report->hasExcel()){
            $view->with('excelUrl', $this->excelUrl($name, $request));
        }
        return $view->with('request', $request);
    }


    /**
     * Output a report as an Excel spreadsheet
     *
     * @param string $name - the name of the report to return
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
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
