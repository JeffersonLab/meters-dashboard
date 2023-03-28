<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'start' => 'required|date',
            'end' => 'date',
            'chart' => 'required',
            'model_id' => 'required',

        ];
    }
}
