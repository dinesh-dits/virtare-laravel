<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ExportReportRequestService;

class ExportReportRequestController extends Controller
{
    // Add Export Request
    public function addExportRequest(Request $request)
    {
        return (new ExportReportRequestService)->insertExportRequest($request);
    }

    // Add PDF Export Request
    public function addPdfExportRequest(Request $request)
    {
        return (new ExportReportRequestService)->insertPdfExportRequest($request);
    }
}
