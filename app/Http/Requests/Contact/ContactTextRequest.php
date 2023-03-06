<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;


class ContactTextRequest extends FormRequest
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
            'message'=> 'required',
        ];
    }

    public function messages()
    {
        return [
            'message.required' => 'message must be required',
        ];
    }
}
