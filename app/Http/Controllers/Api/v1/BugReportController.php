<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\Api\BugReportService;
use Illuminate\Http\Request;


class BugReportController extends Controller
{

   // List Bug Report
   public function bugReportList(Request $request,$bugReportId = null)
   {
    return (new BugReportService)->bugReportList($request,$bugReportId);
   }

   // Add Bug Report
   public function createBugReport(Request $request)
   {
    return (new BugReportService)->createBugReport($request);
   }

   // Delete Bug Report
   public function deleteBugReport(Request $request,$bugReportId)
   {
    return (new BugReportService)->deleteBugReport($request,$bugReportId);
   }
   // Delete Bug Report
   public function screenList(Request $request)
   {
    return (new BugReportService)->screenList($request);
   }
}
