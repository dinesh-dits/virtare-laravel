<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\TimeLogService;
use App\Services\Api\TimelineService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class TimeLogController extends Controller
{
    // List Timelog
    public function listTimeLog(Request $request, $id = null)
    {
        return (new TimeLogService)->timeLogList($request, $id);
    }

    // Update Timelog
    public function updateTimeLog(Request $request, $id)
    {
        return (new TimeLogService)->timeLogUpdate($request, $id);
    }

    // Delete Timelog
    public function deleteTimeLog(Request $request, $id)
    {
        return (new TimeLogService)->timeLogDelete($request, $id);
    }

    // Add Patient Timelog
    public function addPatientTimeLog(Request $request, $entityType, $id = null, $timelogId = null)
    {
        return (new TimeLogService)->patientTimeLogAdd($request, $entityType, $id, $timelogId);
    }

    // List Patient Timelog
    public function listPatientTimeLog(Request $request, $entityType, $id = null, $timelogId = null)
    {
        return (new TimeLogService)->patientTimeLogList($request, $entityType, $id, $timelogId);
    }

    // Delete Patient Timelog
    public function deletePatientTimeLog(Request $request, $entityType, $id = null, $timelogId)
    {
        return (new TimeLogService)->patientTimeLogDelete($request, $entityType, $id, $timelogId);
    }

    // Timelog Report
    public function timeLogReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "patientTimelog_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::excelTimeLogExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // Change Audit Log
    public function changeAuditLog(Request $request,$id)
    {
        return (new TimeLogService)->auditLogChange($request,$id);
    }
}
