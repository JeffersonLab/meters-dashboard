<?php

namespace App\Livewire;

use App\Models\Meters\Meter;
use Livewire\Component;
use App\Models\Meters\MeterLimit as MeterLimitModel;

class MeterLimit extends Component
{
    // Form Fields
    public $lolo;
    public $low;
    public $high;
    public $hihi;

    // What type of meter are we dealing with
    public string $type;

    // The meter id in case we need to create a new limit
    public int $meterId;

    public bool $enableEdit = false;

    public ?MeterLimitModel $limit;


    public function mount(int $meterId){
        $this->meterId = $meterId;
        $this->prepareFormFields();
    }

    public function render()
    {
        return view('livewire.meter-limit')
            ->with([
                'label' => $this->label(),
                'isEditable' => $this->isEditable(),
            ]);
    }

    public function toggleEdit() {
        $this->enableEdit = !$this->enableEdit;
    }

    protected function prepareFormFields(){
        if ($this->limit){
            $this->lolo = $this->limit->lolo;
            $this->low = $this->limit->low;
            $this->high = $this->limit->high;
            $this->hihi = $this->limit->hihi;
        }
    }

    public function createLimit($meterId) {
        $meter = Meter::find($meterId);
        $this->limit = $meter->meterLimits()->create([
            'meter_id' => $meterId,
            'field' => $this->fieldName(),
            'interval' => 60 * 60 *24,       // just per-day right now
            'source' => 'web',
        ]);
        $this->prepareFormFields();
        if ($this->limit && $this->limit->id) {
            $this->enableEdit = true;
        }
    }

    public function save() {
        $this->limit->fill([
          'lolo' => is_numeric($this->lolo) ? $this->lolo: null,
          'low' => is_numeric($this->low) ? $this->low: null,
          'high' => is_numeric($this->high) ? $this->high: null,
          'hihi' => is_numeric($this->hihi) ? $this->hihi: null,
        ]);
        if (! $this->limit->save()){
            dd($this->limit->errors());
        }
        $this->enableEdit = false;
    }


    public function isEditable(): bool {
        return $this->enableEdit;
    }

    protected function fieldName() : ?string {
        switch($this->type){
            case 'water' : return 'gal';
            case 'power' : return 'totkWh';
            case 'gas'   : return 'ccf';
            default: return null;
        }
    }

    public function label(){
        switch($this->type){
            case 'water' : return 'gal/day';
            case 'power' : return 'kWh/day';
            case 'gas'   : return 'ccf/day';
            default: return $this->limit->field . '/day';
        }

    }
}
