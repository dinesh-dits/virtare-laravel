<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class NewPasswordRequest extends FormRequest
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
            'code' => array('required'),
            'newPassword' => 'required|min:6|required_with:confirmNewPassword|same:confirmNewPassword',
            'confirmNewPassword' => 'required|min:6'
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Code is required.',
            'newPassword.required' => 'New password is required.',
            'confirmNewPassword.required' => 'Confirm new password is required.',
        ];
    }
}
