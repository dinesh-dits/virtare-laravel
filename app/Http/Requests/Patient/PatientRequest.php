<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Propaganistas\LaravelPhone\Rules\Phone;
class PatientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        $patient_udid = request()->segment(2);
        $post = request()->all();
        if (!empty($patient_udid)) {
            $patient = Patient::where('udid', $patient_udid)->first();
            if (isset($post["isApp"]) && $post["isApp"] == true) {

                if ($post['email']) {
                    return [
                       // 'email' => 'required|unique:users,' . $patient['userId'] . ',id,deletedAt,NULL',
                       'email' => 'required|email|unique:users,email,'.$patient['userId'],
                        'firstName' => 'required',
                        'lastName' => 'required',
                        'dob' => 'required',
                    ];
                } else {
                    return [
                        'firstName' => 'required',
                        'lastName' => 'required',
                        'dob' => 'required',
                    ];
                }
            } else {

                return [
                    'email' => 'required|email|unique:users,email,'.$patient['userId'],
                  //  'email' => 'required|unique:users,' . $patient['userId'] . ',id,deletedAt,NULL',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'dob' => 'required',
                    'phoneNumber' => ['required', 'numeric', 'digits:10'],
                ];
            }
        } else {

            if (isset($post["isApp"]) && $post["isApp"] === true) {

                if ($post['email']) {
                    return [
                        'email' => 'required|unique:users,email,NULL,id,deletedAt,NULL',
                        'firstName' => 'required',
                        'lastName' => 'required',
                        'dob' => 'required',
                    ];
                } else {
                    return [
                        'firstName' => 'required',
                        'lastName' => 'required',
                        'dob' => 'required',
                    ];
                }
            } else {

                return [
                    'email' =>    'required|unique:users,email,NULL,id,deletedAt,NULL',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'dob' => 'required',
                    'phoneNumber' => ['required', 'numeric', 'digits:10'],
                ];
            }
        }
    }

    public function messages()
    {
        return [
            'email.required' => 'Patient Email is required',
            'email.unique' => 'Patient Email must be unique',
            'firstName.required' => 'Patient firstName is required',
            'lastName.required' => 'Patient lastName is required',
            'dob.required' => 'Patient Date Of Birth is required',
            'phoneNumber.required' => 'Patient phoneNumber is required',
        ];
    }
}
