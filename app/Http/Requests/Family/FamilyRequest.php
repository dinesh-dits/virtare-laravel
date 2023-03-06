<?php

namespace App\Http\Requests\Family;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;

class FamilyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $family = User::where([['email', request()->email], ['roleId', 6]])->first();
        if ($family) {
            return [
                'email' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                'phoneNumber' => ['required', 'numeric', 'digits:10'],
                'gender' => 'required',
                'relation' => 'required',
                'vitalAuthorization' => 'required',
                'messageAuthorization' => 'required',
            ];
        } else {
            return [
                'email' => 'unique:users,email',
                'firstName' => 'required',
                'lastName' => 'required',
                'phoneNumber' => ['required', 'numeric', 'digits:10'],
                'gender' => 'required',
                'relation' => 'required',
                'vitalAuthorization' => 'required',
                'messageAuthorization' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [
            'email.required' => 'Email must be required',
            'email.unique' => 'Email must be unique',
            'firstName.required' => 'firstName is required',
            'lastName.required' => 'lastName is required',
            'phoneNumber.required' => 'Phone Number is required',
            'gender.required' => 'Gender is required',
            'relation.required' => 'Relation is required',
            'vitalAuthorization.required' => 'Vital Authorization is required',
            'messageAuthorization.required' => 'Message Authorization is required',
        ];
    }
}
