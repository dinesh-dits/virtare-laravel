<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Patient\PatientReferral;

class PatientReferalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = request()->segment(4);
        $email = PatientReferral::where('udid', $id)->first();
        if ($email) {
            return [
                'referralEmail' => 'unique:referrals,email,' . $email['id']
            ];
        } else {
            if (request()->referralEmail || request()->firstName || request()->lastName || request()->referralPhoneNumber || request()->referralDesignation) {
                return [
                    'referralEmail' => 'required|unique:referrals,email',
                ];
            } else {
                return [];
            }
        }
    }

    public function messages()
    {
        return [
            'referralEmail.unique' => 'Email must be unique',
            'referralEmail.required' => 'Email is required',
        ];
    }
}
