<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\RolePermissionService;
use App\Services\Api\ExportReportRequestService;

class RolePermissionController extends Controller
{

    // List Role
    public function getAllRoles(Request $request, $id = null)
    {
        return (new RolePermissionService)->getAllRoles($request, $id);
    }
    public function roleList(Request $request, $id = null)
    {
        return (new RolePermissionService)->roleList($request, $id);
    }

    // Add Role
    public function createRole(RoleRequest $request)
    {
        return (new RolePermissionService)->createRole($request);
    }

    // Update Roles
    public function updateRole(Request $request, $id)
    {
        return (new RolePermissionService)->updateRole($request, $id);
    }

    // Delete Role
    public function deleteRole(Request $request, $id)
    {
        return (new RolePermissionService)->deleteRole($request, $id);
    }

    // List Roles and Permission
    public function rolePermissionList(Request $request, $id)
    {
        return (new RolePermissionService)->rolePermissionList($request, $id);
    }

    // Add Role and Permission
    public function createRolePermission(Request $request, $id)
    {
        return (new RolePermissionService)->createRolePermission($request, $id);
    }

    // List Permission 
    public function permissionsList(Request $request)
    {
        return (new RolePermissionService)->permissionsList($request);
    }

    // Update Role Permission
    public function rolePermissionEdit($id)
    {
        return (new RolePermissionService)->rolePermissionEdit($id);
    }

    // Role and Permission Report
    public function roleAndPermissionReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "roleAndPermission_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::roleAndPermissionExcelExport($request);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }
}
