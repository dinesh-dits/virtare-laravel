<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
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
            'deviceToken' => 'required',
            'title'=>'required',
            'body'=>'required',
        ];
    }

    public function messages()
    {
        return [
            'deviceToken.required' => 'Device Token must be required',
            'title.required' => 'title must be required',
            'body.required' => 'body must be required',
        ];
    }

    
}
