<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;


class AppointmentRequest extends FormRequest
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
            'staffId' => 'required',
            'patientId' => 'required',
            'startDate' => 'required',
            'durationId' => 'required',
            'appointmentTypeId' => 'required',
            'note' => 'required',
            'startTime' => 'required',
            'flag' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'staffId.required' => 'Care Coordinator is required',
            'patientId.required' => 'Patient is required',
            'startDate.required' => 'Start Date is required',
            'durationId.required' => 'Duration Time is required',
            'appointmentTypeId.required' => 'Type of Visit  is required',
            'note.required' => 'Note is required',
            'startTime.required' => 'Start Time is required',
            'flag.required' => 'Flag is required',
        ];
    }
}
