<?php

namespace App\Http\Requests\Escalation;

use Illuminate\Foundation\Http\FormRequest;

class EscalationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    { 
        return [
            'referenceId' => 'required',
            'type' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'referenceId.required' => 'Patient is required',
            'type.required' => 'Escalation Type is required',
        ];
    }
}
