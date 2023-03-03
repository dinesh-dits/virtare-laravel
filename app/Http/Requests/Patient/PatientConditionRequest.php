<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientConditionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'condition' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'condition.required' => 'Condition is required',
        ];
    }
}
