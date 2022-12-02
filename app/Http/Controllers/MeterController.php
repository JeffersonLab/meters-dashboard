<?php

namespace App\Http\Controllers;

use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;


class MeterController extends Controller
{
    /**
     * Display the meter index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {
        return View::make('meters.index')
            ->with('meters', Meter::all());
    }

    /**
     * Display a meter
     *
     * @param Meter $meter
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Meter $meter, Request $request) {

        JavaScript::put([
                'currentModel' => $meter,
                'metersData' => $this->meterData(collect()->push($meter)),
        ]);

        return View::make('meters.item')
            ->with('meter', $meter);

    }


    /**
     * Display a status page for the given meter type
     *
     * @return \Illuminate\Contracts\View\View|void
     */
    public function monitor($type) {
        switch ($type){
            case 'power' : return $this->powerStatus();
            case 'water' : return $this->meterStatus('water');
            case 'gas' : return $this->meterStatus('gas');
            case 'power-kwh' : return $this->powerStatusKwh();
            case 'power-kw' : return $this->powerStatusKw();
            case 'power-volt-avg' : return $this->powerStatusVoltAverage();
            case 'water-gal' : return $this->waterStatusGal();
            case 'water-gpm' : return $this->waterStatusGpm();
            case 'gas-ccf' : return $this->gasStatusCcf();
            case 'gas-ccfpm' : return $this->gasStatusCcfpm();

        }

    }

    public function meterStatus($type){
        $meters = Meter::where('type',$type)->orderBy('epics_name')->get();
        return $this->meterStatusView($meters);
    }

    protected function meterStatusView(Collection $meters){
        JavaScript::put([
            'metersData' => $this->meterData($meters),
        ]);
        return View::make('status.meters')
            ->with('meters', $this->meterData($meters));
    }

    // Special handling for building the list of power meters so that we exclude substation meters
    public function powerStatus()
    {
        $meters = Meter::with('building')
            ->where('type','power')
            ->orderBy('epics_name')
            ->get()
            ->filter(function($meter){
                return $meter->building && strtolower($meter->building->type) == 'building';
            })->values();
        return $this->meterStatusView($meters);
    }


    public function powerStatusKwh()
    {

        return View::make('status.odometer')
            ->with('meters', Meter::where('type','power')->orderBy('epics_name')->get())
            ->with('meterType','power')
            ->with('label', 'kWh')
            ->with('field', 'totkWh')
            ->with('referenceDate', Carbon::today()->day(1)); //first of month;
    }

    public function powerStatusKw()
    {
        return View::make('status.dynameter')
            ->with('meters', Meter::where('type','power')
                //->whereIn('epics_name',['33MVA','40MVA'])   // useful limit during debugging
                ->orderBy('epics_name')->get())
            ->with('meterType','power')
            ->with('label', 'kW')
            ->with('field', ':totkW');
    }

    public function powerStatusVoltAverage()
    {
        $meters = Meter::where('type','power')
            //->whereIn('epics_name',['33MVA','40MVA'])
            ->orderBy('epics_name')->get();
        $data = [];
        foreach ($meters as $meter){
            $pv = $meter->epics_name.':llVolt';
            $data[] = [
                'id' => $meter->id,
                'epics_name' => $meter->epics_name,
                'label' => $meter->getPresenter()->reportLabel(),
                'url' => $meter->getPresenter()->url(),
                'pv' => $pv,
                'comm_err' => $meter->epics_name.':commErr',
                'stat' => $pv.'.STAT',
                'alarm_limits' => $meter->getHelper()->alarmLimits(),
                'alarm_state' => 100,
            ];
        }

        return View::make('status.voltage')
            ->with('data', $data)
            ->with('meters', $meters)
            ->with('meterType','power')
            ->with('label', 'llVolt')
            ->with('field', ':llVolt');
    }


    public function waterStatusGal()
    {
        return View::make('status.odometer')
            ->with('meters', Meter::where('type','water')->orderBy('epics_name')->get())
            ->with('meterType','water')
            ->with('label','Gallons')
            ->with('field', 'gal')
            ->with('referenceDate', Carbon::today()->day(1)); //first of month
    }

    public function waterStatusGpm()
    {
        return View::make('status.dynameter')
            ->with('meters', Meter::where('type','water')->orderBy('epics_name')->get())
            ->with('meterType','water')
            ->with('label', 'GPM')
            ->with('field', ':galPerMin');
    }

    public function gasStatusCcf()
    {
        return View::make('status.odometer')
            ->with('meters', Meter::where('type','gas')->orderBy('epics_name')->get())
            ->with('meterType','gas')
            ->with('label','CCF')
            ->with('field', 'ccf')
            ->with('referenceDate', Carbon::today()->day(1)); //first of month
    }

    public function gasStatusCcfpm()
    {
        return View::make('status.dynameter')
            ->with('meters', Meter::where('type','gas')->orderBy('epics_name')->get())
            ->with('meterType','gas')
            ->with('label','CCFPM')
            ->with('field', ':ccfPerMin');
    }



}
