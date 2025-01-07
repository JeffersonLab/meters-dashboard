<?php

namespace App\Http\Controllers;

use App\Charts\ChartFactory;
use App\Http\Requests\ChartRequest;
use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use App\Models\Meters\VirtualMeter;
use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

class ApiController extends Controller
{
    /**
     * Return data for a canvasJs chart
     *
     * @return \Illuminate\Http\Response;
     */
    public function meterChartData(ChartRequest $request)
    {
        try {
            $meter = Meter::find($request->input('model_id'));
            $chart = ChartFactory::make($request->input('chart'), $meter, $request);

            return $this->response($chart->toArray());
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Return data for a meter
     *
     * @return \Illuminate\Http\Response;
     */
    public function meterTableData(HttpRequest $request)
    {
        try {
            $meter = Meter::find($request->input('model_id'));

            return $this->response([
                'request' => $request->all(),
                'columns' => $meter->dataColumns(),
                'data' => $meter->dataBetween(
                    Carbon::parse($request->input('start')),
                    Carbon::parse($request->input('end')),
                    $request->input('granularity', 'daily'))->all(),
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Return data for a canvasJs chart
     *
     * @return \Illuminate\Http\Response;
     */
    public function buildingChartData(ChartRequest $request)
    {
        try {
            $building = Building::find($request->input('model_id'));
            $chart = ChartFactory::make($request->input('chart'), $building, $request);

            return $this->response($chart->toArray());
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Return data for a canvasJs chart
     *
     * @return \Illuminate\Http\Response;
     */
    public function reportChartData(HttpRequest $request)
    {
        try {
            $meter = new VirtualMeter;
            $meter->setMeters(Meter::whereIn('id', Arr::wrap($request->input('model_id')))->get());
            $chart = ChartFactory::make('multimeter', $meter, $request);

            return $this->response($chart->toArray());
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Returns a json success response.
     *
     * @return mixed
     */
    public function response($data)
    {
        $struct['status'] = 'ok';
        $struct['data'] = $data;

        $options = 0;
        if (Request::input('pretty')) {
            $options = JSON_PRETTY_PRINT;
        }

        $response = response()->json($struct, 200, [], $options);

        if (Request::has('jsonp')) {
            $response->setCallback(Request::input('jsonp'));
        }

        return $response;
    }

    /**
     * Returns a json error response.
     *
     * @return mixed
     */
    public function error(string $msg, int $code = 404)
    {
        $struct['status'] = 'fail';
        $struct['message'] = $msg;

        $options = 0;
        if (Request::input('pretty')) {
            $options = JSON_PRETTY_PRINT;
        }

        $response = response()->json($struct, $code, [], $options);

        if (Request::has('jsonp')) {
            $response->setCallback(Request::input('jsonp'));
        }

        return $response;
    }
}
