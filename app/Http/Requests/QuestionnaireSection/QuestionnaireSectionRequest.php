<?php

namespace App\Http\Requests\QuestionnaireSection;

use Illuminate\Foundation\Http\FormRequest;

class QuestionnaireSectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'sectionName' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sectionName.required' => 'Question is required',
        ];
    }
}
