<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

abstract class Controller extends BaseController
{

    protected function meterData(Collection $meters)
    {
        return $meters->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => $item->type,
                'epics_name' => $item->epics_name,
                'building' => $item->housed_by,
                'model_number' => $item->model_number,
                'pvs' => $item->pvFields(),

            ];
        })->sortBy('epics_name')->values();
    }
}
