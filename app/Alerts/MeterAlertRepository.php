<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 1/30/18
 * Time: 10:43 AM
 */

namespace App\Alerts;

use App\Exceptions\MeterDataException;
use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

/**
 * Class MeterAlertRepository
 *
 * Repository used to retrieve MeterAlerts.
 */
class MeterAlertRepository
{
    /**
     * @var Collection
     */
    protected $alerts;

    /**
     * MeterAlertRepository constructor.
     */
    public function __construct()
    {
        $this->alerts = new Collection;
        $this->populateAlerts();
    }

    /**
     * Populates the alerts collection from Nagios Service alerts
     */
    public function populateAlerts()
    {
        $this->populateWaterFlowAlerts();
        //@TODO cleanup data before enabling
        //$this->populateDataHasZerosAlerts();
    }

    /**
     * Populates the alerts collection with alerts related to
     * excessive or insufficient water consumption
     */
    public function populateWaterFlowAlerts()
    {
        $meters = Meter::with('meterLimits')
            ->where('type', 'water')->get()->all();
        foreach ($meters as $meter) {
            try {
                $consumedYesterday = $this->gallonsConsumed($meter, Carbon::yesterday(), Carbon::today());
                if (! $meter->withinLimits('gal', $consumedYesterday)) {
                    $alert = new MeterAlert($meter, 'warning');
                    $alert->description = 'Threshold';

                    // First we check and assign warnings for the minor thresholds
                    if ($meter->isTooHighMinor('gal', $consumedYesterday)) {
                        $alert->message = sprintf('%s gal consumed on %s exceeded threshold of %s gal/day',
                            Number::format($consumedYesterday),
                            Carbon::yesterday()->format('l, F jS'),
                            Number::format($meter->fieldLimits('gal')->high));
                        $alert->status = 'warning';
                    }
                    if ($meter->isTooLowMinor('gal', $consumedYesterday)) {
                        $alert->message = sprintf('%s gal consumed on %s was below threshold of %s gal/day',
                            Number::format($consumedYesterday),
                            Carbon::yesterday()->format('l, F jS'),
                            Number::format($meter->fieldLimits('gal')->low));
                        $alert->status = 'warning';
                    }
                    // Then we check and assign warnings for the major thresholds, possibly overwriting minor thresholds above
                    // First we check and assign warnings for the minor thresholds
                    if ($meter->isTooHighMajor('gal', $consumedYesterday)) {
                        $alert->message = sprintf('%s gal consumed on %s exceeded threshold of %s gal/day',
                            Number::format($consumedYesterday),
                            Carbon::yesterday()->format('l, F jS'),
                            Number::format($meter->fieldLimits('gal')->hihi));
                        $alert->status = 'critical';
                    }
                    if ($meter->isTooLowMajor('gal', $consumedYesterday)) {
                        $alert->message = sprintf('%s gal consumed on %s was below threshold of %s gal/day',
                            Number::format($consumedYesterday),
                            Carbon::yesterday()->format('l, F jS'),
                            Number::format($meter->fieldLimits('gal')->lolo));
                        $alert->status = 'critical';
                    }

                    $this->pushAlert($alert);
                }
            } catch (MeterDataException $e) {
                $alert = new MeterAlert($meter, 'warning');
                $alert->description = 'Data Anomaly';
                $alert->message = $e->getMessage();
            }
        }
    }

    public function gallonsConsumed(Meter $meter, Carbon $fromDate, Carbon $toDate)
    {
        return $meter->consumedBetween('gal', $fromDate, $toDate);
    }

    public function populateDataHasZerosAlerts()
    {
        // Water Meters
        foreach ($this->waterMetersWithZeroGalReadings() as $meter) {
            $alert = new MeterAlert($meter, 'warning');
            $alert->description = 'Data Anomaly';
            $alert->message = sprintf('%s has zero value for gal at %s', $meter->epics_name, $meter->date);
            $this->pushAlert($alert);
        }
    }

    public function waterMetersWithZeroGalReadings()
    {
        return Meter::join('water_meter_data', 'meters.id', '=', 'water_meter_data.meter_id')
            ->where('gal', 0)
            ->where('water_meter_data.date', '>', 'meters.begins_at')
            ->get();
    }

    public function pushAlert(MeterAlert $alert)
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
