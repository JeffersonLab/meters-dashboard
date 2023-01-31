<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/13/18
 * Time: 4:17 PM
 */

namespace App\Models\Meters;

class MeterHelper
{
    protected $meter;

    /**
     * MeterHelper constructor.
     */
    public function __construct(Meter $meter)
    {
        $this->meter = $meter;
    }
}
