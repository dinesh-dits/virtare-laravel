<?php

namespace App\Http\Requests\CustomForm;

use Illuminate\Foundation\Http\FormRequest;

class CustomFormAssignRequest extends FormRequest
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
                'values' => 'required',
              //  'users' => 'required|array|min:1',
                //"users.*"  => "required|array|min:0"           
        ];
    }

    public function messages()
    {
        return [
            'form_id.required' => 'Please provide form id',
            'values.required' => 'Please select users to assign ',
           // 'users.array' => 'Invalid format for users data',
           // 'users.*.required' => 'Atleast one user required to assign.',
        ];
    }
}
