<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class ProviderLocationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',

        ];
    }

    public function messages()
    {
        return [
            'country.required' => 'Country is required',
            'state.required' => 'State is required',
            'city.required' => 'City is required',
        ];
    }
}
