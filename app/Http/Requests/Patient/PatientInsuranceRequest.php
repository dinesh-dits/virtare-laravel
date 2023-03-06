<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientInsuranceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'insurance' => 'required_without:insuranceNumber,insuranceName',
            // 'insuranceNumber' => 'required_if:insurance.*',
            // 'expirationDate' => 'required_if:insurance.*',
            // 'insuranceName' => 'required_if:insurance.*',
        ];
    }

    public function messages()
    {
        return [
             'insurance.required_without' => 'Insurance Number is required','Insurance Name is required',
            
            // 'expirationDate.required' => 'Expiration Date must be required',
            // 'insuranceName.required' => 'Insurance Name must be required',
        ];
    }
}
