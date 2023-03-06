<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff\Staff;
use App\Models\StaffContact\StaffContact;
use Illuminate\Foundation\Http\FormRequest;

class StaffContactRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $udid = request()->segment(5);
        $staff = StaffContact::where('udid', $udid)->first();
        if ($staff) {
            return [
                'email' => 'required|unique:staffContacts,email,' . $staff['id'],
                'firstName' => 'required',
                'phoneNumber' => ['required', 'numeric', 'digits:10']
            ];
        } else {
            return [
                'email' => 'required|unique:staffContacts,email,NULL,id,deletedAt,NULL',
                'firstName' => 'required',
                'phoneNumber' => ['required', 'numeric', 'digits:10']
            ];
        }
    }

    public function messages()
    {
        return [
            'email.required' => 'Email is required',
            'email.unique' => 'Email must be unique',
            'firstName.required' => 'firstName is required',
            'phoneNumber.required' => 'phoneNumber is required',
            'phoneNumber.numeric' => 'Phone Number is numeric',
        ];
    }
}
