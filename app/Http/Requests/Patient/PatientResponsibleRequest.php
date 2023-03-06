<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient\PatientResponsible;
use Illuminate\Foundation\Http\FormRequest;

class PatientResponsibleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // $patient = request()->segment(2);
        // if($patient){
        //     $responsible = PatientResponsible::where('udid', $patient)->first();
        //     // dd($responsible['patientResponsibleId']);
        //     return [
        //         'email' => 'unique:patientResponsibles,email,'.$responsible['patientResponsibleId'] . ',patientResponsibleId',
                
        //     ];
        // }else{
            return [
                //'email' => 'unique:patientResponsibles,email',
            ];
        // }
    }

    public function messages()
    {
        return [
            'email.unique' => 'Email Must Be Unique',
        ];
    }
}
