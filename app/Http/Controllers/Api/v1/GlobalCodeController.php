<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\GlobalCodeService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;
use App\Http\Requests\GlobalCode\GlobalCodeRequest;
use App\Http\Controllers\Controller;

class GlobalCodeController extends Controller
{
    // List Global Code Category
    public function globalCodeCategory(Request $request, $id = null)
    {
        return (new GlobalCodeService)->globalCodeCategoryList($request, $id);
    }

    // List Global Code
    public function globalCode(Request $request, $id = null)
    {
        return (new GlobalCodeService)->globalCodeList($request, $id);
    }

    // Add Global Code
    public function createGlobalCode(GlobalCodeRequest $request)
    {
        return (new GlobalCodeService)->globalCodeCreate($request);
    }

    // Update Global Code
    public function updateGlobalCode(Request $request, $id)
    {
        return (new GlobalCodeService)->globalCodeUpdate($request, $id);
    }

    // delete Global Code
    public function deleteGlobalCode(Request $request, $id)
    {
        return (new GlobalCodeService)->globalCodeDelete($request, $id);
    }

    // Global Code Start End Date
    public function globalStartEndDate(Request $request)
    {
        return (new GlobalCodeService)->getGlobalStartEndDate($request);
    }

    // Global Code Report
    public function globalCodeReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "globalCode_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::globalCodeExcelExport($request);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }
}
