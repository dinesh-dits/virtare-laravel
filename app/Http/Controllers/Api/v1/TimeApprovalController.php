<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\TimeApprovalService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class TimeApprovalController extends Controller
{
    // Add Time Approval
    public function addTimeApproval(Request $request)
    {
        return (new TimeApprovalService)->addTimeApproval($request);
    }

    // List Time Approval
    public function listTimeApproval(Request $request,$id=null)
    {
        return (new TimeApprovalService)->listTimeApproval($request,$id);
    }

    // Update Time Approval
    public function updateTimeApproval(Request $request,$id)
    {
        return (new TimeApprovalService)->updateTimeApproval($request,$id);
    }

    // Update Time Approval Multiple Id's
    public function updateTimeApprovalMultiple(Request $request)
    {
        return (new TimeApprovalService)->updateTimeApprovalMultiple($request);
    }

    // timelog Approval Report
    public function timelogApprovalReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "timelogApproval_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::timelogApprovalExcelExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }
}
