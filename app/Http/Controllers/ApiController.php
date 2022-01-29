<?php

namespace App\Http\Controllers;

use App\Charts\ChartFactory;
use App\Http\Requests\ChartRequest;
use App\Meters\Building;
use App\Meters\Meter;
use App\Meters\VirtualMeter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class ApiController extends Controller
{

    /**
     * Return data for a canvasJs chart
     *
     * @param ChartRequest $request
     * @return \Illuminate\Http\Response;
     */
    public function meterChartData(ChartRequest $request)
    {
        try{
            $meter = Meter::find($request->input('model_id'));
            $chart = ChartFactory::make($request->input('chart'), $meter, $request);
            return $this->response($chart->toArray());
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }

    }


    /**
     * Return data for a canvasJs chart
     *
     * @param ChartRequest $request
     * @return \Illuminate\Http\Response;
     */
    public function buildingChartData(ChartRequest $request)
    {
        try{
            $building = Building::find($request->input('model_id'));
            $chart = ChartFactory::make($request->input('chart'), $building, $request);
            return $this->response($chart->toArray());
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * Return data for a canvasJs chart
     *
     * @param Request $request
     * @return \Illuminate\Http\Response;
     */
    public function reportChartData(Request $request)
    {
        try{
            $meter = new VirtualMeter();
            $meter->setMeters(Meter::whereIn('id',array_wrap($request->input('model_id')))->get());
            $chart = ChartFactory::make('multimeter', $meter, $request);
            return $this->response($chart->toArray());
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }

    }


    /**
     * Returns a json success response.
     *
     * @param $data
     * @return mixed
     */
    public function response($data){
        $struct['status'] = 'ok';
        $struct['data'] = $data;

        $options = 0;
        if (Input::get('pretty')){
            $options = JSON_PRETTY_PRINT;
        }

        $response = response()->json($struct, 200, [], $options);

        if (Input::has('jsonp')){
            $response->setCallback(Input::get('jsonp'));
        }

        return $response;
    }


    /**
     * Returns a json error response.
     *
     * @param  string $msg
     * @param  int $code
     * @return mixed
     */
    public function error($msg, $code=404){
        $struct['status'] = 'fail';
        $struct['message'] = $msg;

        $options = 0;
        if (Input::get('pretty')){
            $options = JSON_PRETTY_PRINT;
        }

        $response = response()->json($struct, $code, [], $options);

        if (Input::has('jsonp')){
            $response->setCallback(Input::get('jsonp'));
        }
        return $response;
    }
}
