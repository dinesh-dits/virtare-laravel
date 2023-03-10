<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\PatientNewService;
use App\Http\Requests\PatientNew\PatientNewRequest;

class PatientNewController extends Controller
{
    public function addPatient(PatientNewRequest $request)
    {
        return (new PatientNewService)->patientAdd($request);
    }

    public function listPatient(Request $request, $id = null)
    {
        return (new PatientNewService)->patientList($request, $id);
    }

    public function updatePatient(Request $request, $id)
    {
        return (new PatientNewService)->patientUpdate($request, $id);
    }

    public function deletePatient(Request $request, $id)
    {
        return (new PatientNewService)->patientDelete($request, $id);
    }

    public function assignInventoryPatient(Request $request, $id)
    {
        return (new PatientNewService)->inventoryPatientAssign($request, $id);
    }

    public function getPatientInventory(Request $request, $id,$inventoryId=null)
    {
        return (new PatientNewService)->patientInventoryGet($request, $id,$inventoryId);
    }

    public function deletePatientInventory(Request $request, $id,$inventoryId)
    {
        return (new PatientNewService)->patientInventoryDelete($request, $id,$inventoryId);
    }
}
