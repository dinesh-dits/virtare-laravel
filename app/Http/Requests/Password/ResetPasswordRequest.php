<?php

namespace App\Http\Requests\Password;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'password'=>'required|min:8',
            'confirmPassword'=>'required|same:password'
        ];
    }

    public function messages()
    {
        return [
            'password.required'=>"Password Required",
            'password.min'=>"Password must be at least 8 Characters",
            'confirmPassword.required'=>"Confirm Password Required",
            'confirmPassword.same'=>"Confirm Password Doesn't Match with Password",
        ];
    }

    
}
