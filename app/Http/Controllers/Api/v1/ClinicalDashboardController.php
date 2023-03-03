<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ClinicalDashboardService;


class ClinicalDashboardController extends Controller
{

    // Escalation Count
    public function escalationCount(Request $request)
    {
        return (new ClinicalDashboardService)->countEscalation($request);
    }

    // Task Count
    public function taskCount(request $request)
    {
        return (new ClinicalDashboardService)->countTask($request);
    }

    // Ptaient Flag Count
    public function listPatientFlagCount(Request $request)
    {
        return (new ClinicalDashboardService)->patientFlagListCount($request);
    }

    // Appointment Count
    public function appointmentCount(Request $request)
    {
        return (new ClinicalDashboardService)->countAppointment($request);
    }

    // Patient Count
    public function patientCount(Request $request)
    {
        return (new ClinicalDashboardService)->count($request);
    }
}
