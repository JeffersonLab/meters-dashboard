<?php

namespace App\Http\Controllers;

use App\Models\Buildings\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Laracasts\Utilities\JavaScript\JavaScriptFacade as JavaScript;

class CoolingTowerController extends Controller
{
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
     * Display a building
     */
    public function show(Building $building, Request $request): \Illuminate\View\View
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

    public function substationSummary(): \Illuminate\View\View
    {
        return view('buildings.substation_summary');
    }
}
