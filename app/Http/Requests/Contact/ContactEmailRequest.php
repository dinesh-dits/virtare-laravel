<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;


class ContactEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function rules()
    {
        return [
            'email'=> 'required',
            'name'=> 'required'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'email  must be required',
            'name.required' => 'name  must be required',
        ];
    }
}
