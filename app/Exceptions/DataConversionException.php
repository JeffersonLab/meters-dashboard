<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 2/13/18
 * Time: 11:19 AM
 */

namespace App\Exceptions;

class DataConversionException extends \Exception
{
    public $meter;

    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
