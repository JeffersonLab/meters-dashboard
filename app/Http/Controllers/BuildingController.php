<?php

namespace App\Http\Controllers;

use App\Models\Buildings\Building;
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
    public function index()
    {
        JavaScript::put([
            'buildingsData' => $this->buildingStatusData(Building::all()),
        ]);

        return View::make('buildings.index');
    }

    protected function buildingStatusData(Collection $buildings)
    {
        return $buildings->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'epics_name' => $item->name,
                'building' => $item->name,
                'buildingNumber' => $item->building_num,
                'pvs' => [':alrmSum'],
            ];
        });
    }

    /**
     * Display the buildings index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function siteMap()
    {
        return View::make('buildings.map');
    }

    /**
     * Display a building
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Building $building, Request $request)
    {
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

    public function substationSummary()
    {
        return view('buildings.substation_summary');
    }
}
