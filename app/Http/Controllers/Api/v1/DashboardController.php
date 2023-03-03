<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Api\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    // Staff Network Count
    public function staffNetwork(Request $request)
    {
        return (new DashboardService)->staffNetwork($request);
    }

    // Staff Specialization Count
    public function staffSpecialization(Request $request)
    {
        return (new DashboardService)->staffSpecialization($request);
    }

    // Send Message
    public function sendMessage(Request $request)
    {
        return (new DashboardService)->sendMessage($request);
    }

    // List Timezone
    public function getTimezone(Request $request)
    {
        return (new DashboardService)->getTimezone($request);
    }

    // List Email Log
    public function getEmailLogs(Request $request,$id)
    {
        return (new DashboardService)->getEmailLogs($request,$id);
    }
}
