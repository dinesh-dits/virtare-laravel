<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientMedicalHistoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'history' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'history.required' => 'History is required',
        ];
    }
}
