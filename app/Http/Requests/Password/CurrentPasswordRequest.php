<?php

namespace App\Http\Requests\Password;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class CurrentPasswordRequest extends FormRequest
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
        $password = Hash::make($this->request->get('currentPassword'));
        return [
            'currentPassword'=>'password:'.Hash::check($password, auth()->user()->password),
            'newPassword'=>'required',
            'confirmPassword'=>'required|same:newPassword'
        ];
    }

    public function messages()
    {
        return [
            'currentPassword.password'=>"Current Password Doesn't Match",
            'newPassword.required'=>"New Password Required",
            'confirmPassword.required'=>"Confirm Password Required",
            'confirmPassword.same'=>"Confirm Password Doesn't Match with New Password",
        ];
    }

    
}
