<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\PatientStaffService;

class PatientStaffController extends Controller
{
    // Assign Staff to Patient 
    public function assignStaff(Request $request, $id, $patientStaffId = null)
    {
        return (new PatientStaffService)->assignStaffToPatient($request, $id, $patientStaffId);
    }

    // Get assigned Staff to Patient
    public function getAssignStaff(Request $request, $id, $patientStaffId = null)
    {
        return (new PatientStaffService)->getAssignStaffToPatient($request, $id, $patientStaffId);
    }

    // Delete Assigned Staff to Patient
    public function deleteAssignStaff(Request $request, $id, $patientStaffId)
    {
        return (new PatientStaffService)->deleteAssignStaffToPatient($request, $id, $patientStaffId);
    }

    // Global Search
    public function search(Request $request)
    {
        return (new PatientStaffService)->search($request);
    }
}
