<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/13/18
 * Time: 4:16 PM
 */

namespace App\Models\Meters;

class WaterMeterHelper extends MeterHelper
{
    public function gallonsConsumed(Carbon $fromDate, Carbon $toDate)
    {
        $query = $this->meter->dataTable()
            ->select(['date', 'gal'])
            ->where('meter_id', $this->meter->id)
            ->where('date', '>=', $fromDate)
            ->where('date', '<=', $toDate)
            ->whereNotNull('gal')
            ->orderBy('date', 'asc');
        $data = $query->get();
        $firstVal = $data->first() ? $data->first()->gal : null;
        $lastVal = $data->last() ? $data->last()->gal : null;

        if (! $firstVal === null || $lastVal === null) {
            $message = sprintf('Encountered null data in the gal column');
            throw new MeterDataException($message, $this->meter);
        }

        if ($firstVal == 0 && $fromDate->greaterThan($this->meter > begins_at)) {
            $message = sprintf('Encountered unexpected 0 in the gal column at %s', $fromDate);
            throw new MeterDataException($message, $this->meter);
        }

        if ($firstVal > $lastVal) {
            $message = sprintf('Value of gal decreased from %s between %s', $fromDate, $toDate);
            throw new MeterDataException($message, $this->meter);
        }

        return $lastVal - $firstVal;
    }
}
