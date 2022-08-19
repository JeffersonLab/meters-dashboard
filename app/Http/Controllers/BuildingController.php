<?php

namespace App\Http\Controllers;

use App\Models\Buildings\Building;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class BuildingController extends Controller
{

    /**
     * Display the buildings index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {
        return View::make('buildings.index');
    }

    /**
     * Display a building
     *
     * @param Building $building
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Building $building, Request $request) {

        if ($request->has('month') && $request->has('year')){
            $date = Carbon::create($request->input('year'), $request->input('month'),1,0,0,0);
            $building->reporter()->beginning($date);
            $building->reporter()->ending($date->addMonth());
        }

        JavaScript::put([
            'currentApiUrl' => route('buildings.chart_data'),
            'currentDateRange' => [
                'begins' => $building->reporter()->beginsAt(),
                'ends' => $building->reporter()->endsAt(),
            ],
            'currentModel' => $building,
            'metersData' => $this->meterData($building->meters()->get()),
        ]);

        return View::make('buildings.item')
            ->with('building', $building);

    }

    public function substationSummary(){
        return view('buildings.substation_summary');
    }

    protected function meterData(Collection $meters){
        $data = [];
        foreach ($meters as $meter){
            $data[] = [
                'id' => $meter->id,
                'type' => $meter->type,
                'epics_name' => $meter->epics_name,
                'pvs' => $meter->pvFields(),
            ];
        }
        return $data;
    }
}
