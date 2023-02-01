<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/13/18
 * Time: 11:19 AM
 */

namespace App\Exceptions;

use App\Models\Meters\Meter;

class MeterDataException extends \Exception
{
    public $meter;

    public function __construct(string $message, Meter $meter)
    {
        parent::__construct($message);
        $this->meter = $meter;
    }
}
