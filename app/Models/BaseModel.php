<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jlab\LaravelUtilities\BaseModel as Model;

class BaseModel extends Model
{
    use HasFactory;

    public function primaryKeyValue(): int
    {
        return $this->getAttribute($this->primaryKey);
    }
}
