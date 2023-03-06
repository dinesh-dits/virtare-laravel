<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\VitalService;
use App\Http\Controllers\Controller;
use App\Services\Api\PdfGeneratorService;
use App\Services\Api\ExportReportRequestService;

class VitalController extends Controller
{

  // List Vital Type Field 
  public function listVitalTypeField(Request $request, $id = null)
  {
    return (new VitalService)->VitalTypeFieldList($request, $id);
  }

  // Vital PDF Report
  public function vitalPdfReport(Request $request,$id)
  {
    if ($id) {
        $reportType = "vital_pdf";
        $checkReport = ExportReportRequestService::checkPdfRequest($id, $reportType);
        if ($checkReport) {
          PdfGeneratorService::vitalPdfExport($request,$id);
        } else {
            return response()->json(['message' => "User not Access to download Report."], 403);
        }
    } else {
        return response()->json(['message' => "invalid URL."], 400);
    }
  }
}
