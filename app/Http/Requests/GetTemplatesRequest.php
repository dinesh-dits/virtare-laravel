<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetTemplatesRequest extends FormRequest
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
    public function prepareForValidation()
    {
        $this->merge([
            "type" => $this->headers->get("type"),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            "type" => "required|in:sendMail,sendSMS,commMail,commSms",
        ];
    }
    public function messages()
    {
        return [
            'type.required' => 'Invalid type selected.',          
        ];
    }
}
