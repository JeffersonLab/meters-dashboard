<?php

namespace App\Http\Controllers;

use App\Alerts\MeterAlertRepository;
use App\Alerts\ServiceAlertRepository;
use App\Utilities\NagiosHostlist;
use App\Utilities\NagiosServicelist;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Collection;

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

            $serviceAlerts = $serviceAlertRepository->alerts()
                ->sortBy(function ($alert, $key) {
                    return $alert->meter()->epics_name;
            });;
            $consumptionAlerts = $meterAlertRepository->alerts()
                ->sortBy(function ($alert, $key) {
                    return $alert->meter()->epics_name;
            });;

            return View::make('alerts.table')
                ->with('serviceAlerts', $serviceAlerts)
                ->with('consumptionAlerts', $consumptionAlerts);
        } catch (\Exception $e) {
            return View::make('alerts.error')
                ->with('message', $e->getMessage());
        }
    }
}
