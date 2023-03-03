<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommunicationTemplatesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [         
            'subject' => 'required',
            'messageBody' => 'required',
            'templateName'=>'required',
            'entityType'=>'required'
        ];
    }

    public function messages()
    {
        return [
            'subject.required' => 'Subject is required',
            'messageBody.required' => 'Message is required',
            'templateName.required' => 'Template name is required',
            'entityType.required' => 'Entity type name is required'
        ];
    }
}
