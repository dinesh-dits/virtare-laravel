<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;

class AssignQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'questionId' => 'required',
            'questionnaireTempleteId' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'question.required' => 'Question id is required',
            'questionnaireTempleteId.required' => 'Questionnaire Template id is required',
        ];
    }
}
