<?php

namespace App\Http\Requests\Client;

use App\Models\Client\Client;
use Illuminate\Foundation\Http\FormRequest;


class ClientRequest extends FormRequest
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
        if (request()->segment(3)) {
            $other = [];
        } else {
            $other = [
                'contactPerson.firstName' => 'required',
                'contactPerson.lastName' => 'required',
                'contactPerson.email' => 'required|unique:users,email,NULL,id,deletedAt,NULL',
                'contactPerson.phoneNumber' => ['required', 'numeric', 'digits:10'],
            ];
        }
        $input = [
            'friendlyName' => 'required',
            'legalName' => 'required',
            'npi' => ['required', 'numeric', 'digits:10'],
            'stateId' => 'required',
            'city' => 'required',
            'zipCode' => ['required', 'numeric', 'digits:5'],
            'startDate' => 'required',
            'endDate' => 'required',
            'addressLine1' => 'required',
            'phoneNumber' => ['required', 'numeric', 'digits:10'],
            'contractTypeId' => 'required',
            'programs' => 'required',
        ];

        return array_merge($input, $other);
    }

    public function messages()
    {
        return [
            'friendlyName.required' => 'Required',
            'legalName.required' => 'Required',
            'legalName.unique' => 'Required',
            'npi.required' => 'Required',
            'stateId.required' => 'Required',
            'city.required' => 'Required',
            'zipCode.required' => 'Required',
            'startDate.required' => 'Required',
            'endDate.required' => 'Required',
            'addressLine1.required' => 'Required',
            'phoneNumber.required' => 'Required',
            'contractTypeId.required' => 'Required',
            'programs.required' => 'Required',
            'contactPerson.firstName.required' => 'Required',
            'contactPerson.lastName.required' => 'Required',
            'contactPerson.email.required' => 'Required',
            'contactPerson.email.unique' => 'Email already taken',
            'contactPerson.phoneNumber.required' => 'Required',
        ];
    }
}
