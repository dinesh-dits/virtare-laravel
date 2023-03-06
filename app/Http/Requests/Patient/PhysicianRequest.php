<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient\PatientPhysician;
use Illuminate\Foundation\Http\FormRequest;

class PhysicianRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = request()->physicianId;        
        if (!empty($id)) {
            $patient = PatientPhysician::where('udid', $id)->first();
            if ($patient) {
                return [
                    'email' => 'unique:users,email,' . $patient['userId'] . 'udid',
                ];
            }
            return [
                'email' => 'unique:users,email',
            ];
        } else {
            if (request()->email || request()->name || request()->designation) {
                return [
                    'email' => 'required|unique:users,email',
                    'name' => 'required',
                    'designation' => 'required',
                ];
            } else {
                return [];
            }
        }
    }

    public function messages()
    {
        return [
            'email.unique' => 'Email must be unique',
            'emal.required' => 'Emal is required',
            'name.required' => 'Name is required',
            'designation.required' => 'Designation must be required',
        ];
    }
}
