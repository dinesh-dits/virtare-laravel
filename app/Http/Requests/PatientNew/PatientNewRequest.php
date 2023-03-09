<?php

namespace App\Http\Requests\PatientNew;

use Illuminate\Foundation\Http\FormRequest;

class PatientNewRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [
            'primaryLanguageId' => 'required',
            'contactMethodId' => 'required',
            'bestTimeToCallId' => 'required',
            'height' => 'required',
            'weight' => 'required',
            'placeOfServiceId' => 'required',
            'user.email' => 'required',
            'user.timeZoneId' => 'required',
            'contact.firstName' => 'required',
            'contact.lastName' => 'required',
            'contact.dob' => 'required',
            'contact.phoneNumber' => 'required',
            'contact.genderId' => 'required',
            'address.line1' => 'required',
            'address.line2' => 'required',
            'address.stateId' => 'required',
            'address.city' => 'required',
            'address.zipCode' => 'required',
            'insurance.insuranceNameId' => 'required',
            'insurance.insuranceNumber' => 'required',
            'insurance.startDate' => 'required',
            'insurance.endDate' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'primaryLanguageId.required' => 'Primary language is required',
            'contactMethodId.required' => 'Contact methodId is required',
            'bestTimeToCallId.required' => 'Best time to call is required',
            'height.required' => 'Height is required',
            'weight.required' => 'Weight is required',
            'placeOfServiceId.required' => 'Place of service is required',
            'user.email.required' => 'Email is required',
            'user.timeZoneId.required' => 'Time zone is required',
            'contact.firstName.required' => 'First name is required',
            'contact.lastName.required' => 'Last name is required',
            'contact.dob.required' => 'dob is required',
            'contact.phoneNumber.required' => 'Phone number is required',
            'contact.genderId.required' => 'Gender is required',
            'address.line1.required' => 'Line 1 is required',
            'address.line2.required' => 'Line 2 is required',
            'address.stateId.required' => 'State is required',
            'address.city.required' => 'City is required',
            'address.zipCode.required' => 'Zip code is required',
            'insurance.insuranceNameId.required' => 'Insurance name is required',
            'insurance.insuranceNumber.required' => 'Insurance number is required',
            'insurance.startDate.required' => 'Start date is required',
            'insurance.endDate.required' => 'End date is required',
        ];
    }
}
