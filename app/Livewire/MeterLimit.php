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
        $this->authorize('update', $this->limit);
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
        $this->authorize('create', MeterLimitModel::class);
        $meter = Meter::find($meterId);
        $this->limit = $meter->meterLimits()->create([
            'meter_id' => $meterId,
            'field' => $this->fieldName(),
            'interval' => 60 * 60 *24,       // just per-day right now
            'source' => 'web',
            'lolo' => 0,                     // Negative consumption makes no sense
            'low' => 0,                      // Negative consumption makes no sense
        ]);
        $this->prepareFormFields();
        if ($this->limit && $this->limit->id) {
            $this->enableEdit = true;
        }
    }

    public function save() {

        // If all numeric values are null, we delete the MeterLimit entirely
        if ($this->shouldDelete()) {
            $this->authorize('delete', $this->limit);
            $this->limit->delete();
            $this->limit = null;
            $this->enableEdit = false;
        }else{
            $this->authorize('update', $this->limit);
            $this->limit->fill([
                'lolo' => is_numeric($this->lolo) ? $this->lolo: null,
                'low' => is_numeric($this->low) ? $this->low: null,
                'high' => is_numeric($this->high) ? $this->high: null,
                'hihi' => is_numeric($this->hihi) ? $this->hihi: null,
            ]);
            // The validation below will throw ValidationExceptions that livewire
            // will catch and automatically make available to the blade view
            // in an $errors variable.
            $valid = $this->limit->getValidator()->validate();

            if (! $this->limit->save()){
                $this->addError('form', 'Failed to update limits');
            }else{
                $this->enableEdit = false;
            }
        }
    }


    protected function shouldDelete(){
        // If all four values are non-numeric (NULL, "", "NA",'-', etc.) then there are no valid
        // alert limits and we interpret this as a request to remove the record if it exists in the database.
        return ! (is_numeric($this->lolo) || is_numeric($this->low) || is_numeric($this->high) || is_numeric($this->hihi));
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
