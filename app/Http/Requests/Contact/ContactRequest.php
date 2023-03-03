<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;


class ContactRequest extends FormRequest
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
            'contactTimeId'=> 'required',
            // 'toTime'=> 'required',
            // 'timeZone'=> 'required',
        ];
    }

    public function messages()
    {
        return [
            'contactTimeId.required' => 'Contact Time  must be required',
            // 'toTime.required' => 'To Time  must be required',
            // 'timeZone.required' => 'Timezone  must be required',
        ];
    }
}
