<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\GeneralParameterService;
use App\Services\Api\ExportReportRequestService;

class GeneralParameterController extends Controller
{

    // Add General Parameter Group
    public function addGeneralParameterGroup(Request $request, $id = null)
    {
        return (new GeneralParameterService)->generalParameterAdd($request, $id);
    }

    // List General Parameter Group
    public function listGeneralParameterGroup(Request $request, $id = null)
    {
        return (new GeneralParameterService)->generalParameterGroupList($request, $id);
    }

    // List General Parameter
    public function listGeneralParameter(Request $request, $id)
    {
        return (new GeneralParameterService)->generalParameterList($request, $id);
    }

    // Delete General Parameter Group
    public function deleteGeneralParameterGroup(Request $request, $id)
    {
        return (new GeneralParameterService)->generalParameterGroupDelete($request, $id);
    }

    // Delete General Parameter
    public function deleteGeneralParameter(Request $request, $id)
    {
        return (new GeneralParameterService)->generalParameterDelete($request, $id);
    }

    // General Parameter Report
    public function generalParameterReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "generalParameter_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::generalParameterExcelExport($request);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }
}
