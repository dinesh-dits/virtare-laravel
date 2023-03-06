<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientProgramRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'program' => 'required',
            'onboardingScheduleDate' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'program.required' => 'Program is required',
            'onboardingScheduleDate.required' => 'Onboarding Schedule Date is required',
        ];
    }
}
