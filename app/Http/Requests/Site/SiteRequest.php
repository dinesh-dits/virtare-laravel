<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;


class SiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {
        return [
            'friendlyName'=> 'required',
            'address.stateId'=> 'required_if:virtual,==,false',
            'address.city'=> 'required_if:virtual,==,false',
            'address.zipCode'=>'required_if:virtual,==,false',
            'address.addressLine1'=> 'required_if:virtual,==,false',
        ];
    }

    public function messages()
    {
        return [
            'friendlyName.required' => 'Required',
            'address.stateId.required_if' => 'Required',
            'address.city.required_if' => 'Required',
            'address.zipCode.required_if' => 'Required',
            'address.addressLine1.required_if' => 'Required',
        ];
    }
}
