<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientInventoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'deviceType' => 'required',
            'modelNumber' => 'required',
            'macAddress' => 'required',
            'deviceTime' => 'required',
            'serverTime' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'deviceType.required' => 'Device Type is required',
            'modelNumber.required' => 'Model Number is required',
            'macAddress.required' => 'Mac Address is required',
            'deviceTime.required' => 'Device Time is required',
            'serverTime.required' => 'Server Time is required',
        ];
    }
}
