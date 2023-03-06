<?php

namespace App\Http\Requests\People;

use App\Models\Staff\Staff;
use Illuminate\Foundation\Http\FormRequest;


class PeopleRequest extends FormRequest
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
        $udid = request()->id;
        if (!empty($udid)) {
            $staff = Staff::where('udid', $udid)->first();
            $other = [];
            $email = 'required|unique:users,email,' . $staff['userId'] . ',id,deletedAt,NULL';
        } else {
            $email = 'required|unique:users,email,NULL,id,deletedAt,NULL';
            $other = ['clientId' => 'required'];
        }
        $input = [
            'firstName' => 'required',
            'title' => 'required',
            'lastName' => 'required',
            'roleId' => 'required',
            'timeZoneId' => 'required',
            'specializationId' => 'required',
            'email' => $email,
            'phoneNumber' => ['required', 'numeric', 'digits:10'],
        ];
        return array_merge($input, $other);
    }

    public function messages()
    {
        return [
            'firstName.required' => 'Required',
            'title.required' => 'Required',
            'middleName.required' => 'Required',
            'lastName.required' => 'Required',
            'email.required' => 'Required',
            'email.unique' => 'Email already taken',
            'phoneNumber.required' => 'Required',
            'roleId.required' => 'Required',
            'timeZoneId.required' => 'Required',
            'clientId.required' => 'Required',
            'specializationId.required' => 'Required',
        ];
    }
}
