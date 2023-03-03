<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient\PatientEmergencyContact;
use Illuminate\Foundation\Http\FormRequest;

class PatientEmergencyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $emg_udid = request()->segment(4);

        $emg = PatientEmergencyContact::where('udid', $emg_udid)->first();
        if ($emg) {
            return [
                'emergencyEmail' => 'required|unique:patientEmergencyContacts,email,' . $emg['id'],
                'firstName' => 'required',
                'lastName' => 'required',
                'gender' => 'required',
            ];
        } else {
            $family = PatientEmergencyContact::where('email', request()->emergencyEmail)->first();
            if ($family) {
                return [
                    'emergencyEmail' => 'required|unique:patientEmergencyContacts,email',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'gender' => 'required',
                ];
            } else {
                return [
                    'emergencyEmail' => 'required',
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'gender' => 'required',
                ];
            }
        }
    }

    public function messages()
    {
        return [
            'emergencyEmail.unique' => 'Email must be unique',
            'emergencyEmail.required' => 'Email is required',
            'firstName.required' => 'FirstName is required',
            'lastName.required' => 'LastName is required',
            'gender.required' => 'Gender is required',
        ];
    }
}
