<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\StaffService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StaffRequest;
use App\Services\Api\ExcelGeneratorService;
use App\Http\Requests\Staff\StaffContactRequest;
use App\Http\Requests\Password\ResetPasswordRequest;
use App\Services\Api\ExportReportRequestService;
use App\Http\Requests\Staff\StaffAvailabilityRequest;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param StaffRequest $request
     * @return array
     */

    //  Add Staff
    public function addStaff(StaffRequest $request)
    {
        return (new StaffService)->addStaff($request);
    }

    // List Staff
    public function listStaff(Request $request, $id = null)
    {
        return (new StaffService)->listStaff($request, $id);
    }

    // Update Staff
    public function updateStaff(StaffRequest $request, $id)
    {
        return (new StaffService)->updateStaff($request, $id);
    }

    // Delete Staff
    public function deleteStaff(Request $request, $id)
    {
        return (new StaffService)->staffDelete($request, $id);
    }

    // Update Staff Status
    public function updateStaffStatus(Request $request, $id)
    {
        return (new StaffService)->updateStaffStatus($request, $id);
    }

    // Add Staff Contact
    public function addStaffContact(StaffContactRequest $request, $id)
    {
        return (new StaffService)->addStaffContact($request, $id);
    }

    // List Staff Contact
    public function listStaffContact(Request $request, $id, $staffContactId = null)
    {
        return (new StaffService)->listStaffContact($request, $id, $staffContactId);
    }

    // Update Staff Contact
    public function updateStaffContact(StaffContactRequest $request, $staffId, $id)
    {
        return (new StaffService)->updateStaffContact($request, $staffId, $id);
    }

    // Delete Staff Contact
    public function deleteStaffContact(Request $request, $staffId, $id)
    {
        return (new StaffService)->deleteStaffContact($request, $staffId, $id);
    }

    // Add Staff Availability
    public function addStaffAvailability(StaffAvailabilityRequest $request, $id)
    {
        return (new StaffService)->addStaffAvailability($request, $id);
    }

    // List Staff Availability
    public function listStaffAvailability(Request $request, $id, $staffAvailabilityId = null)
    {
        return (new StaffService)->listStaffAvailability($request, $id, $staffAvailabilityId);
    }

    // Update Staff Availability
    public function updateStaffAvailability(Request $request, $staffId, $id)
    {
        return (new StaffService)->updateStaffAvailability($request, $staffId, $id);
    }

    // Delete Staff Availability
    public function deleteStaffAvailability(Request $request, $staffId, $id)
    {
        return (new StaffService)->deleteStaffAvailability($request, $staffId, $id);
    }

    // Add Staff Role
    public function addStaffRole(Request $request, $id)
    {
        return (new StaffService)->addStaffRole($request, $id);
    }

    // List staff Role
    public function listStaffRole(Request $request, $id)
    {
        return (new StaffService)->listStaffRole($request, $id);
    }

    // Update Staff Role
    public function updateStaffRole(Request $request, $staffId, $id)
    {
        return (new StaffService)->updateStaffRole($request, $staffId, $id);
    }

    // Delete Staff Role
    public function deleteStaffRole(Request $request, $staffId, $id)
    {
        return (new StaffService)->deleteStaffRole($request, $staffId, $id);
    }

    // Add Staff Provider
    public function addStaffProvider(Request $request, $id)
    {
        return (new StaffService)->addStaffProvider($request, $id);
    }

    // List Staff Provider
    public function listStaffProvider(Request $request, $id)
    {
        return (new StaffService)->listStaffProvider($request, $id);
    }

    // Update Staff Provider
    public function updateStaffProvider(Request $request, $staffId, $id)
    {
        return (new StaffService)->updateStaffProvider($request, $staffId, $id);
    }

    // Delete Staff Provider
    public function deleteStaffProvider(Request $request, $staffId, $id)
    {
        return (new StaffService)->deleteStaffProvider($request, $staffId, $id);
    }

    // Staff Specialization Count
    public function specializationCount()
    {
        return (new StaffService)->specializationCount();
    }

    // Staff Netowrk Count
    public function networkCount()
    {
        return (new StaffService)->networkCount();
    }

    // Staff Report
    public function careCoordinatorReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "careCoordinator_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::careCoordinatorExcelExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // Staff Report
    public function specialistsReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "specialists_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::specialistsExcelExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // Reset Staff Password
    public function resetStaffPassword(ResetPasswordRequest $request, $id)
    {
        return (new StaffService)->resetStaffPassword($request, $id);
    }

    // Add Staff Multiple Locations
    public function addStaffLocation(Request $request, $id)
    {
        return (new StaffService)->staffLocationAdd($request, $id);
    }

    // List Staff Multiple Locations
    public function listStaffLocation(Request $request, $id, $locationId = null)
    {
        return (new StaffService)->staffLocationList($request, $id, $locationId);
    }

    // Delete Staff Multiple Locations
    public function deleteStaffLocation(Request $request, $id, $locationId)
    {
        return (new StaffService)->staffLocationDelete($request, $id, $locationId);
    }

    // Add Staff Multiple Programs
    public function addStaffProgram(Request $request, $id)
    {
        return (new StaffService)->staffProgramAdd($request, $id);
    }

    // List Staff Multiple Programs
    public function listStaffProgram(Request $request, $id, $programId = null)
    {
        return (new StaffService)->staffProgramList($request, $id, $programId);
    }

    // Delete Staff Multiple Programs
    public function deleteStaffProgram(Request $request, $id, $programId)
    {
        return (new StaffService)->staffProgramDelete($request, $id, $programId);
    }

    //Staff profile update
    public function staffProfileUpdate(Request $request, $id)
    {
        return (new StaffService)->staffProfileUpdate($request, $id);
    }

    //Staff Group
    public function listStaffGroup(Request $request, $id, $groupId = null)
    {
        return (new StaffService)->staffGroupList($request, $id, $groupId);
    }

    //Staff Client Locations
    public function getLocation(Request $request)
    {
        return (new StaffService)->locationGet($request);
    }
}
