<?php

namespace App\Models\Meters;

use App\Models\BaseModel;

class RolloverEvent extends BaseModel
{
    public static $rules = [
        'meter_id' => 'required',
        'field' => 'required | in:gal,totkWh,ccf',
        'rollover_at' => 'date',
        'rollover_accumulated' => 'integer',

    ];

    public $fillable = [
        'meter_id',
        'field',
        'rollover_at',
        'rollover_accumulated',
    ];

    protected $casts = [
        'rollover_at' => 'datetime',
    ];

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }
}
