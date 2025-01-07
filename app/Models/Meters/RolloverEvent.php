<?php

namespace App\Models\Meters;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected function casts(): array
    {
        return [
            'rollover_at' => 'datetime',
        ];
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }
}
