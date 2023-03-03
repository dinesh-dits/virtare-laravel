<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff\Staff;
use Illuminate\Foundation\Http\FormRequest;

class StaffRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $staff_udid = request()->segment(2);
        if (!empty($staff_udid)) {
            $staff = Staff::where('udid', $staff_udid)->first();
            return [
                'email' => 'required|unique:users,email,' . $staff['userId'] . ',id,deletedAt,NULL',
                'firstName' => 'required',
                'lastName' => 'required',
                'genderId' => 'required',
                'phoneNumber' => ['required', 'numeric', 'digits:10'],
                'specializationId' => 'required',
            ];
        } else {

            return [
                'email' => 'required|unique:users,email,NULL,id,deletedAt,NULL',
                'firstName' => 'required',
                'lastName' => 'required',
                'genderId' => 'required',
                'phoneNumber' => ['required', 'numeric', 'digits:10'],
                'specializationId' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [
            'email.required' => 'Staff Email is required',
            'email.unique' => 'This email is already registered, please use a unique email address.',
            'firstName.required' => 'Staff first name is required',
            'lastName.required' => 'Staff last name is required',
            'genderId.required' => 'Staff gender is required',
            'phoneNumber.required' => 'Staff Phone Number is required',
            'phoneNumber.numeric' => 'Staff Phone Number is numeric',
            'specializationId.required' => 'Staff specialization is required',
        ];
    }
}
