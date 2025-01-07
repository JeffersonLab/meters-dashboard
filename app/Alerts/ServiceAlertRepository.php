<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 1/30/18
 * Time: 10:43 AM
 */

namespace App\Alerts;

use App\Models\Meters\Meter;
use App\Utilities\NagiosServicelist;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ServiceAlertRepository
 *
 * Repository used to retrieve ServiceAlerts.
 */
class ServiceAlertRepository
{
    /**
     * @var NagiosServicelist
     */
    protected $nagiosServiceList;

    /**
     * @var Collection
     */
    protected $alerts;

    public function __construct(NagiosServicelist $nagiosServicelist)
    {
        $this->nagiosServiceList = $nagiosServicelist;
        $this->alerts = new Collection;
        $this->populateAlerts();
    }

    /**
     * Populates the alerts collection from Nagios Service alerts
     */
    public function populateAlerts()
    {
        $this->nagiosServiceList->getData();
        foreach ($this->nagiosServiceList->filterByNotStatus('ok') as $hostname => $services) {
            foreach ($services as $service) {
                $this->pushAlert(new ServiceAlert($service));
            }
        }
    }

    /**
     * Populates the alerts collection with alerts related to
     * excessive or insufficient water consumption
     */
    public function populateWaterFlowAlerts()
    {
        $meters = Meter::where('epics_name', 'CH_Grnd_Wtr_Dis_Sml')->get()->all();
        foreach ($meters as $meter) {
            $consumedYesterday = $meter->dataTable()
                ->select(DB::raw('max(gal) - min(gal) as consumed'))
                ->where('meter_id', $meter->id)
                ->where('date', '<=', Carbon::today())
                ->where('date', '>=', Carbon::yesterday())
                ->get();
            dd($consumedYesterday);
        }
    }

    public function pushAlert(ServiceAlert $alert)
    {
        $this->alerts->push($alert);
    }

    /**
     * Returns a collection of alerts
     */
    public function alerts()
    {
        return $this->alerts;
    }
}
