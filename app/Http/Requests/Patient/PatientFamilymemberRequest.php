<?php

namespace App\Http\Requests\Patient;

use App\Models\User\User;
use App\Models\Patient\Patient;
use Illuminate\Foundation\Http\FormRequest;

class PatientFamilymemberRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        $data = request()->segment(4);
        $patient = Patient::where('udid', request()->segment(2))->first();
        $family = User::where([['email', request()->familyEmail], ['roleId', 6]])->whereHas('familyMember', function ($query) use ($patient) {
            $query->where('patientId', $patient->id);
        })->first();
        if ($data) {
            if ($family) {
                return [
                    'familyEmail' => 'required|unique:users,email,' . $family['id'] . ',id,deletedAt,NULL',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'familyGender' => 'required',
                    'relation' => 'required',
                ];
            } 
            else {
                return [
                    'familyEmail' => 'required',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'familyGender' => 'required',
                    'relation' => 'required',
                ];
            }
        } else {
            if($family){
                return [
                    'familyEmail' => 'required|unique:users,email,NULL,id,deletedAt,NULL',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'familyGender' => 'required',
                    'relation' => 'required',
                ];
            }else{
                return [
                    'familyEmail' => 'required',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'familyGender' => 'required',
                    'relation' => 'required',
                ];
            }
           
        }
    }

    public function messages()
    {
        return [
            'familyEmail.unique' => 'Email must be unique',
            'familyEmail.required' => 'Email is required',
            'firstName.required' => 'firstName is required',
            'lastName.required' => 'lastName is required',
            'familyGender.required' => 'Gender is required',
        ];
    }
}
