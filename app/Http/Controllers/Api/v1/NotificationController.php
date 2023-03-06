<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\NotificationService;

class NotificationController extends Controller
{
    // Appointment Notifications
    public function appointmentNotification(Request $request)
    {
        return (new NotificationService)->appointmentNotification($request);
    }

    // New Patient Flags
    public function newPatientFlag(Request $request)
    {
        return (new NotificationService)->removeNewPatientFlag($request);
    }

    // Update IsRead
    public function updateIsRead(Request $request,$id=null)
    {
        return (new NotificationService)->isReadUpdate($request,$id);
    }

    // List IsRead
    public function listIsRead(Request $request,$id=null)
    {
        return (new NotificationService)->isReadList($request,$id);
    }

    // Non Compliance
    public function nonCompliance(Request $request)
    {
        return (new NotificationService)->nonCompliance($request);
    }

     //patient program reminder
    public function patientProgramReminder(Request $request)
  {
    return (new NotificationService)->patientProgramReminder($request);
  }

  public function messageReadReminder(Request $request)
  {
    return (new NotificationService)->messageReadReminder($request);
  }
}
