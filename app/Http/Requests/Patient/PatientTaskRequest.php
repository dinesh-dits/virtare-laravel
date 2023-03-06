<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class PatientTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required',
            'priority' => 'required',
            'startTimeDate' => 'required',
            'dueDate' => 'required',
            'description' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Title is required',
            'priority.required' => 'Priority is required',
            'startTimeDate.required' => 'StartTimeDate is required',
            'dueDate.required' => 'DueDate is required',
            'description.required' => 'Description is required',
        ];
    }
}
