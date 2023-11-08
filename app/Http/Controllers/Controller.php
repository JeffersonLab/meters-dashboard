<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

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
