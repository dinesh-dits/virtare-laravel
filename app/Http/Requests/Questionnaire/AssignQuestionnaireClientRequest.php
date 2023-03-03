<?php

namespace App\Http\Requests\Questionnaire;

use Illuminate\Foundation\Http\FormRequest;

class AssignQuestionnaireClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'questionnaireTemplateId' => 'required',
            'referenceId' => 'required',
            'entityType' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'questionnaireTemplateId.required' => 'Questionnaire Template id is required',
            'referenceId.required' => 'Referece id is required',
            'entityType.required' => 'Entity Type id is required',
        ];
    }
}
