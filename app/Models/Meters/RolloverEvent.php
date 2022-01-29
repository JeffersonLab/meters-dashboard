<?php

namespace App\Models\Meters;


use App\Models\BaseModel;

class RolloverEvent extends BaseModel
{

    public static $rules = array(
        'meter_id' => 'required',
        'field' => 'required | in:gal,totkWh,ccf',
        'rollover_at' => 'date',
        'rollover_accumulated' => 'integer',

    );
    public $fillable = array(
        'meter_id',
        'field',
        'rollover_at',
        'rollover_accumulated',
    );

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['rollover_at'];


    public function meter(){
        return $this->belongsTo(Meter::class);
    }

}
