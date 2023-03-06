<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientMedicalRoutineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'medicine' => 'required',
            'frequency' => 'required',
            'startDate' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'medicine.required' => 'Medicine is required',
            'frequency.required' => 'Frequency is required',
            'startDate.required' => 'Start Date is required',
        ];
    }
}
