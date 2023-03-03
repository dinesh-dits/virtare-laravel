<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\CPTCodeService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class CPTCodeController extends Controller
{

    // List CPT code
    public function listCPTCode(Request $request, $id = NULL)
    {
        return (new CPTCodeService)->listCPTCode($request, $id);
    }

    // Add CPT Code
    public function createCPTCode(Request $request)
    {
        return (new CPTCodeService)->createCPTCode($request);
    }

    // Update CPT Code
    public function updateCPTCode(Request $request, $id)
    {
        return (new CPTCodeService)->updateCPTCode($request, $id);
    }

    // Update Status CPT Code
    public function updateCPTCodeStatus(Request $request, $id)
    {
        return (new CPTCodeService)->updateCPTCodeStatus($request, $id);
    }

    // Delete CPT Code
    public function deleteCPTCode(Request $request, $id)
    {
        return (new CPTCodeService)->deleteCPTCode($request, $id);
    }

    // CPT Code Reports
    public function cptCodeReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "cptCode_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::excelCptCodeExport($request);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // List CPT Code Service
    public function cptCodeList(Request $request)
    {
        return (new CPTCodeService)->cptCodeList($request);
    }

    // CPT Code Service By Id
    public function cptCodeListDetail(Request $request, $id)
    {
        return (new CPTCodeService)->cptCodeListDetail($request, $id);
    }

    // Update CPT Code Service Status
    public function cptCodeStatusUpdate(Request $request, $id = null)
    {
        return (new CPTCodeService)->cptCodeStatusUpdate($request, $id);
    }

    public function getNextBillingDetail(Request $request, $id = null)
    {
        return (new CPTCodeService)->getNextBillingDetail($request, $id);
    }

    public function processNextBillingDetail(Request $request)
    {
        return (new CPTCodeService)->processNextBillingDetail($request);
    }

    public function insertNextBillingServiceDetail(Request $request)
    {
        return (new CPTCodeService)->insertNextBillingServiceDetail($request);
    }

    // List CPT Code Activity
    public function cptCodeActivity(Request $request)
    {
        return (new CPTCodeService)->cptCodeActivity($request);
    }

    // Get Next Billing 
    public function cptNextBillingForCall(Request $request)
    {
        return (new CPTCodeService)->cptNextBillingForCall($request);
    }

    // CPT Code Billing Report
    public function cptBillingReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "cptBilling_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::cptBillingReportExcelExport($request);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }
}
