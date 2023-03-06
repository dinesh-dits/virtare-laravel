<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'address' => 'required',
            // 'countryId' => 'required',
            'stateId' => 'required',
            'city' => 'required',
            'zipcode' => 'required',
            'phoneNumber' => ['required', 'numeric', 'digits:10'],
            // 'tagId' => 'required',

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            'address.required' => 'Addess is required',
            // 'countryId.required' => 'Country is required',
            'stateId.required' => 'State is required',
            'city.required' => 'City is required',
            'zipcode.required' => 'Zip Code is required',
            'phoneNumber.required' => 'Phone Number is required',
            // 'tagId.required' => 'Tag is required',
        ];
    }
}
