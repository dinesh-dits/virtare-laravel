<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationSendRequest extends FormRequest
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
            'user' => 'required',
            'title'=>'required',
            'message'=>'required',
        ];
    }

    public function messages()
    {
        return [
            'user.required' => 'user must be required',
            'title.required' => 'title must be required',
            'message.required' => 'message must be required',
        ];
    }
}
