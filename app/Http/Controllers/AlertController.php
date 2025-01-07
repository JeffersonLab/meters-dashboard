<?php

namespace App\Http\Controllers;

use App\Alerts\MeterAlertRepository;
use App\Alerts\ServiceAlertRepository;
use App\Utilities\NagiosHostlist;
use App\Utilities\NagiosServicelist;
use Illuminate\Support\Facades\View;

class AlertController extends Controller
{
    /**
     * Display the buildings index page
     */
    public function index(NagiosHostlist $hostlist, NagiosServicelist $servicelist)
    {
        try {
            $serviceAlertRepository = new ServiceAlertRepository($servicelist);
            $meterAlertRepository = new MeterAlertRepository;

            $alerts = $serviceAlertRepository->alerts();
            $alerts = $alerts->merge($meterAlertRepository->alerts());
            $alerts = $alerts->sortBy(function ($alert, $key) {
                return $alert->meter()->epics_name;
            });

            return View::make('alerts.table')
                ->with('alerts', $alerts);
        } catch (\Exception $e) {
            return View::make('alerts.error')
                ->with('message', $e->getMessage());
        }
    }
}
