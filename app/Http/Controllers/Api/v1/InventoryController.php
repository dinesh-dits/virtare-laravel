<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\InventoryService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class InventoryController extends Controller
{
    // List Inventory
    public function index(request $request, $id = NULL)
    {
        if (!empty($id)) {
            return (new InventoryService)->geInventoryById($id);
        } else {
            return (new InventoryService)->index($request);
        }
    }

    // Add Inventory
    public function store(Request $request)
    {
        return (new InventoryService)->store($request);
    }

    // Update Inventory
    public function update(Request $request, $id)
    {
        return (new InventoryService)->update($request, $id);
    }

    // Delete Inventory
    public function destroy($id)
    {
        return (new InventoryService)->destroy($id);
    }

    // List Device Model
    public function getModels(Request $request)
    {
        return (new InventoryService)->getModels($request);
    }

    // Inventoy Report
    public function inventoryReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "inventory_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::inventoryExcelExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // List Device Model
    public function getManufacture(Request $request)
    {
        return (new InventoryService)->manufactureGet($request);
    }
} 
