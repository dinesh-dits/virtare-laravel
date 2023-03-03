<?php

namespace App\Http\Requests\CareTeam;

use Illuminate\Foundation\Http\FormRequest;

class CareTeamUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
        if (request()->input('teamHeadId') == '0') {
            return [
                'name' => 'required',
                'clientId' => 'required',
                'siteId' => 'required',
                'teamHeadId' => 'required',
                'programs' => 'required',
                'contactPerson.firstName' => 'required',
                'contactPerson.title' => 'required',
                'contactPerson.lastName' => 'required',
                'contactPerson.roleId' => 'required',
                'contactPerson.timeZoneId' => 'required',
                'contactPerson.email' => 'required|unique:users,email',
                'contactPerson.phoneNumber' => ['required', 'numeric', 'digits:10'],
            ];
        }
        return [
            'name' => 'required',
            'clientId' => 'required',
            'siteId' => 'required',
            'teamHeadId' => 'required',
            'programs' => 'required',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Required',
            'programs.required' => 'Required',
            'contactPerson.email.email' => 'Required',
            'contactPerson.email.unique' => 'Email already taken',
        ];
    }
}
