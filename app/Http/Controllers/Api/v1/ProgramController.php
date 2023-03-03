<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ProgramService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class ProgramController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */

  //  List Program
  public function listProgram(Request $request, $id = null)
  {
    return (new ProgramService)->programList($request, $id);
  }

  // Add Program
  public function createProgram(Request $request)
  {
    return (new ProgramService)->createProgram($request);
  }

  // Update Program
  public function updateProgram(Request $request, $id)
  {
    return (new ProgramService)->updateProgram($request, $id);
  }

  // public function editProgram(Request $request,$id)
  // {
  //   return (new ProgramService)->editProgram($request,$id);
  // }

  // Delete Program
  public function deleteProgram(Request $request, $id)
  {
    return (new ProgramService)->deleteProgram($request, $id);
  }

  // Program Report
  public function programReport(Request $request, $id)
  {
      if ($id) {
          $reportType = "program_report";
          $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
          if ($checkReport) {
              ExcelGeneratorService::programExcelExport($request);
          } else {
              return response()->json(['message' => "User not Access to download Report."], 403);
          }
      } else {
          return response()->json(['message' => "invalid URL."], 400);
      }
  }
}
