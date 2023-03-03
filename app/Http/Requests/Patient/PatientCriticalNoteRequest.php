<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientCriticalNoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'criticalNote' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'criticalNote.required' => 'Note is required',
        ];
    }
}
