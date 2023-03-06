<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\StaffService;
use App\Http\Controllers\Controller;

class StaffPatientController extends Controller
{
    // List Patient
    public function patientList(Request $request,$id = null)
    {
        return (new StaffService)->patientList($request,$id);
    }

    // List Appointment
    public function appointmentList(Request $request, $id = null)
    {
        return (new StaffService)->appointmentList($request, $id);
    }

    // Patient Appointment
    public function patientAppointment(Request $request, $id = null)
    {
        return (new StaffService)->patientAppointment($request, $id);
    }
}
