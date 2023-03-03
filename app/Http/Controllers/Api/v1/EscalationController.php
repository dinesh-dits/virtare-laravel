<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\EscalationService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;
use App\Http\Requests\Escalation\EscalationRequest;

class EscalationController extends Controller
{

    public function addEscalation(EscalationRequest $request)
    {
        return (new EscalationService)->addEscalation($request);
    }

    public function addEscalationAssign(Request $request, $id)
    {
        return (new EscalationService)->escalationAssignAdd($request, $id);
    }

    public function addEscalationAction(Request $request, $id)
    {
        return (new EscalationService)->escalationActionAdd($request, $id);
    }

    public function listEscalationAction(Request $request, $id, $actionId = null)
    {
        return (new EscalationService)->escalationActionList($request, $id, $actionId);
    }

    public function addEscalationEmail(Request $request, $id)
    {
        return (new EscalationService)->escalationEmailAdd($request, $id);
    }

    public function addEscalationNotification(Request $request, $id)
    {
        return (new EscalationService)->addEscalationNotification($request, $id);
    }

    public function listEscalationAssign(Request $request, $id, $assignId = null)
    {
        return (new EscalationService)->escalationAssignList($request, $id, $assignId);
    }

    public function addEscalationDetail(Request $request, $id)
    {
        return (new EscalationService)->escalationDetailAdd($request, $id);
    }

    public function updateEscalation(EscalationRequest $request, $id)
    {
        return (new EscalationService)->updateEscalation($request, $id);
    }

    public function deleteEscalation(Request $request, $id = "")
    {
        return (new EscalationService)->deleteEscalation($request, $id);
    }

    public function listEscalation(Request $request, $id = "")
    {
        return (new EscalationService)->listEscalation($request, $id);
    }

    public function escalationList(Request $request, $id)
    {
        return (new EscalationService)->escalationList($request, $id);
    }

    public function escalationAuditList(Request $request, $id = "")
    {
        return (new EscalationService)->escalationAuditList($request, $id);
    }

    public function resendEscalation(Request $request, $id)
    {
        return (new EscalationService)->escalationResend($request, $id);
    }

    public function auditEscalation(Request $request, $id)
    {
        return (new EscalationService)->escalationAudit($request, $id);
    }

    public function verifyEscalation(Request $request, $id)
    {
        return (new EscalationService)->verifyEscalation($request, $id);
    }

    public function addEscalationActionClose(Request $request, $id)
    {
        return (new EscalationService)->addEscalationActionClose($request, $id);
    }

    public function addEscalationAuditDescription(Request $request, $id)
    {
        return (new EscalationService)->addEscalationAuditDescription($request, $id);
    }

    // Escalation Report
    public function escalationReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "escalation_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::escalationExcelExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // Escalation Report
    public function escalationAuditReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "escalationAudit_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::escalationAuditExcelExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }
}
