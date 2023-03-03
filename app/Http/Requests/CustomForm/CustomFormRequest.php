<?php

namespace App\Http\Requests\CustomForm;

use Illuminate\Foundation\Http\FormRequest;

class CustomFormRequest extends FormRequest
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
            'form_name' => 'required',
            'fields' => 'required|array|min:1',
            "fields.*"  => "required|array|min:0",

        ];
    }
    public function messages()
    {
        return [
            'form_name.required' => 'Please enter form name',
            'fields.required' => 'Please enter form fields',
            'fields.array' => 'Form fields must be an array',
            'fields.*.required' => 'Form fields must have atleast one element',
        ];
    }
}
