<?php

namespace App\Http\Requests\Workflow;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        
        return [
            'title' => 'required',
            'startDate' => 'required',
            'eventId' => 'required'
        ];
            
    }

    public function messages()
    {
        return [
            'title.required' => 'Title is required',
            'startDate.required' => 'Start Date is required',
            'eventId.required' => 'Event is required',
        ];
    }
}
