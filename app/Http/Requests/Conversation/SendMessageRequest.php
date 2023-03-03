<?php

namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'conversationId' => 'required',
            'message' => 'required',
            'type' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'conversationId.required' => 'Conversation Id must be required',
            'message.required' => 'message must be required',
            'type.required' => 'type must be required',

        ];
    }
}
