<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\TimelineService;

class TimelineController extends Controller
{
    // Patient Total Count
    public function patientTotal(Request $request)
    {
        return (new TimelineService)->patientTotal($request);
    }

    // Appointment Total Count
    public function appointmentTotal(Request $request)
    {
        return (new TimelineService)->appointmentTotal($request);
    }
}
