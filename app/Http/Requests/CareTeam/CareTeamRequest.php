<?php

namespace App\Http\Requests\CareTeam;

use Illuminate\Foundation\Http\FormRequest;

class CareTeamRequest extends FormRequest
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
    public function rules(): array
    {

        if (request()->input('teamHeadId') == '0') {
            return [
                'siteId' => 'required',
                'clientId' => 'required',
                'name' => 'required',
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
            'siteId' => 'required',
            'clientId' => 'required',
            'name' => 'required',
            'teamHeadId' => 'required',
            'programs' => 'required',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'siteId.required' => 'Required',
            'clientId.required' => 'Required',
            'name.required' => 'Required',
            'programs.required' => 'Required',
            'contactPerson.email.email' => 'Required',
            'contactPerson.email.unique' => 'Email already taken.',
        ];
    }
}
