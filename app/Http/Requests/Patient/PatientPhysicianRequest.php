<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient\PatientPhysician;
use Illuminate\Foundation\Http\FormRequest;

class PatientPhysicianRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id=request()->segment(4);
        if(!empty($id)){
            $patient = PatientPhysician::where('udid',$id)->first();
            return [
                'email' => 'required|unique:users,email,'.$patient['userId'].'udid',
                'name' => 'required',
                'designation' => 'required',
            ];
        }else{
            return [
                'email' => 'required|unique:users,email',
                'name' => 'required',
                'designation' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [
            'email.unique' => 'Email must be unique',
            'email.required' => 'Email is required',
            'name.required' => 'Name is required',
            'designation.required' => 'Designation is required',
        ];
    }
}
