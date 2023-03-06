<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'question' => 'required',
            'dataTypeId' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'question.required' => 'Question is required',
            'dataTypeId.required' => 'Data Type is required',
        ];
    }
}
