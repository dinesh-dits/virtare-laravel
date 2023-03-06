<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\AppointmentService;
use App\Http\Requests\Appointment\AppointmentRequest;

class AppointmentController extends Controller
{
    // New Appointments
    public function newAppointments(Request $request)
    {
        return (new AppointmentService)->newAppointments($request);
    }

    // Today Apponiments
    public function todayAppointment(Request $request, $id = null)
    {
        return (new AppointmentService)->todayAppointment($request, $id);
    }

    // Add Appointemnt
    public function addAppointment(AppointmentRequest $request, $id = null)
    {
        return (new AppointmentService)->addAppointment($request, $id);
    }

    // List Appointment
    public function appointmentList(request $request, $id = null)
    {
        return (new AppointmentService)->appointmentList($request, $id);
    }

    // List New Appointment
    public function appointmentListNew(request $request, $id)
    {
        return (new AppointmentService)->appointmentListNew($request, $id);
    }

    // Appointment Search
    public function appointmentSearch(request $request)
    {
        return (new AppointmentService)->appointmentSearch($request);
    }

    // Appointment Conference
    public function conferenceAppointment(request $request)
    {
        return (new AppointmentService)->AppointmentConference($request);
    }

    // Appointment Conference Id
    public function conferenceIdAppointment(request $request, $id)
    {
        return (new AppointmentService)->AppointmentConferenceId($request, $id);
    }

    // Update Appointment
    public function updateAppointment(request $request, $id)
    {
        return (new AppointmentService)->appointmentUpdate($request, $id);
    }

    // Cancel Appointment
    public function deleteAppointment(request $request, $id)
    {
        return (new AppointmentService)->appointmentDelete($request, $id);
    }

    // Appointment Calls
    public function appointmentCalls(request $request)
    {
        return (new AppointmentService)->appointmentCalls($request);
    }

    // Update Appointment Status
    public function appointmentStatus(Request $request, $id)
    {
        return (new AppointmentService)->appointmentStatus($request, $id);
    }

    // Appointment Detail
    public function appointmentDetail(Request $request, $id)
    {
        return (new AppointmentService)->appointmentDetail($request, $id);
    }

    //Appoinment status Update
    public function appointmentStatusUpdate(Request $request, $id)
    {
        return (new AppointmentService)->appointmentStatusUpdate($request, $id);
    }
}
