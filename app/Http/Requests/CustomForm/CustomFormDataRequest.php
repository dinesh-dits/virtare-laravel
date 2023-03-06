<?php

namespace App\Http\Requests\CustomForm;

use Illuminate\Foundation\Http\FormRequest;

class CustomFormDataRequest extends FormRequest
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
                'form_id' => 'required',
               // 'values' => 'required',

                //'values' => 'required|array|min:1',
               // "values.*"  => "required|array|min:0"           
        ];
    }

    public function messages()
    {
        return [
            'form_id.required' => 'Please provide form id',
          //  'values.required' => 'Please enter form values',
          //  'values.array' => 'Form values must be an array',
           // 'values.*.required' => 'Form values must have atleast one value',
        ];
    }
}
