<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;


class FileRequest extends FormRequest
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
            'file'=> 'max:2048',
        ];
    }

    public function messages()
    {
        return [
            'file.max' => 'File Must be less than 2MB',
        ];
    }

}
