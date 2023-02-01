<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/4/17
 * Time: 10:06 AM
 */

namespace App\Exceptions;

use App\Models\BaseModel;
use Illuminate\Support\Arr;

class ModelValidationException extends \Exception
{
    protected $model;

    public function __construct(BaseModel $model)
    {
        $this->model = $model;
        parent::__construct($this->errorMessages());
    }

    public function errors()
    {
        return $this->model->errors();
    }

    public function errorMessages()
    {
        return implode("\n", (Arr::flatten($this->errors()->all())));
    }
}
