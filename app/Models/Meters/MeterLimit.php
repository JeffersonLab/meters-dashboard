<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 1/31/18
 * Time: 2:51 PM
 */

namespace App\Models\Meters;

use App\Exceptions\MeterLimitException;
use App\Models\BaseModel;
use Illuminate\Support\Facades\Validator;

/**
 * Class MeterLimit
 */
class MeterLimit extends BaseModel
{
    //@todo custom validator that hi > lo
    //@see https://stackoverflow.com/questions/32036882/laravel-validate-an-integer-field-that-needs-to-be-greater-than-another
    public static $rules = [
        'meter_id' => 'required',
        'field' => 'required | in:gal,totkWh',
        'interval' => 'integer',
        'low' => 'nullable|numeric',
        'high' => 'nullable|numeric',
        'lolo' => 'nullable|numeric',
        'hihi' => 'nullable|numeric',
        'source' => 'required | in:epics,web',
    ];

    public $fillable = [
        'meter_id',
        'field',                //PV to which it applies
        'interval',             //seconds
        'low',                  //minor too low
        'high',                 //minor too high
        'lolo',                 //major too low
        'hihi',                 //major too high
        'source',                //where limit defined
    ];

    public function isWithinMinorLimits($value)
    {
        if ($this->isWithinMajorLimits($value)) {
            if ($this->isTooHighMinor($value) || $this->isTooLowMinor($value)) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function isWithinMajorLimits($value)
    {
        if ($this->isTooHighMajor($value) || $this->isTooLowMajor($value)) {
            return false;
        }

        return true;
    }

    public function isWithinLimits($value)
    {
        if ($this->hasMinorLimits()) {
            return $this->isWithinMinorLimits($value);
        }

        return $this->isWithinMajorLimits($value);
    }

    /**
     * @throws MeterLimitException
     */
    protected function assertHasMinorLimits()
    {
        if (! $this->hasMinorLimits()) {
            throw new MeterLimitException('Minor limits not set');
        }
    }

    /**
     * @throws MeterLimitException
     */
    protected function assertHasMajorLimits()
    {
        if (! $this->hasMajorLimits()) {
            throw new MeterLimitException('Major limits not set');
        }
    }

    /**
     * The low and high limits are both not null.
     */
    public function hasMinorLimits(): bool
    {
        return $this->hasLowerLimitMinor() && $this->hasUpperLimitMinor();
    }

    /**
     * The lolo and hihi limits are both not null.
     */
    public function hasMajorLimits(): bool
    {
        return $this->hasLowerLimitMajor() && $this->hasUpperLimitMajor();
    }

    public function hasUpperLimitMajor()
    {
        return $this->hihi !== null;
    }

    public function hasUpperLimitMinor()
    {
        return $this->high !== null;
    }

    public function hasUpperLimit()
    {
        return $this->hasUpperLimitMajor() || $this->hasUpperLimitMinor();
    }

    public function hasLowerLimitMajor()
    {
        return $this->lolo !== null;
    }

    public function hasLowerLimitMinor()
    {
        return $this->low !== null;
    }

    public function hasLowerLimit()
    {
        return $this->hasLowerLimitMinor() || $this->hasLowerLimitMajor();
    }

    public function isTooHighMajor($value)
    {
        if ($this->hasUpperLimitMajor()) {
            if ($value > $this->hihi) {
                return true;
            }
        }

        return false;
    }

    public function isTooHighMinor($value)
    {
        if ($this->hasUpperLimitMinor()) {
            if ($value > $this->high) {
                return true;
            }
        }

        return false;
    }

    public function isTooLowMajor($value)
    {
        if ($this->hasLowerLimitMajor()) {
            if ($value < $this->lolo) {
                return true;
            }
        }

        return false;
    }

    public function isTooLowMinor($value)
    {
        if ($this->hasLowerLimitMinor()) {
            if ($value < $this->low) {
                return true;
            }
        }

        return false;
    }

    public function isMinorAlarm($value)
    {
        return ($this->isTooLowMinor($value) || $this->isTooHighMinor($value))
                 && ! $this->isMajorAlarm($value);
    }

    public function isMajorAlarm($value)
    {
        $this->isTooLowMajor($value) || $this->isTooHighMajor($value);
    }

    /**
     * Exceeds major or minor upper limit.
     */
    public function isTooHigh($value): bool
    {
        return $this->isTooHighMinor($value) || $this->isTooHighMajor($value);
    }

    /**
     * Exceeds major or minor lower limit.
     */
    public function isTooLow($value): bool
    {
        return $this->isTooLowMinor($value) || $this->isTooLowMajor($value);
    }

    public function getValidator()
    {
        $validator = Validator::make($this->attributes, static::$rules);

       /*
        * The conditional validations below take effect when related values
        * are not null.
        */

        // Every value must be above lolo if it's set
        $validator->sometimes('low', 'gte:lolo', function ($input) {
            return $input->lolo !== null;
        });
        $validator->sometimes('high', 'gte:lolo', function ($input) {
            return $input->lolo !== null;
        });
        $validator->sometimes('hihi', 'gte:lolo', function ($input) {
            return $input->lolo !== null;
        });

        // Both high values must be above low if it's set
        $validator->sometimes('high', 'gte:low', function ($input) {
            return $input->low !== null;
        });
        $validator->sometimes('hihi', 'gte:low', function ($input) {
            return $input->low !== null;
        });

        //validation ensures sane limit pairs if/when set
        $validator->sometimes('hihi', 'gte:high', function ($input) {
            return $input->high !== null;
        });



        return $validator;
    }
}
