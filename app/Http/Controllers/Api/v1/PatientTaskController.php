<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\PatientTaskService;
use App\Http\Requests\Patient\PatientTaskRequest;

class PatientTaskController extends Controller
{
    // List Patient Task
    public function patientTaskList(Request $request , $id, $patientTaskId = null)
    {
     return (new PatientTaskService)->patientTaskList($request,$id , $patientTaskId);
    }

    // Add Patient Task
    public function createPatientTask(PatientTaskRequest $request , $id)
    {
     return (new PatientTaskService)->createPatientTask($request , $id);
    }

    // Update Patient Task
    public function patientTaskUpdate(Request $request , $id, $patientTaskId)
    {
     return (new PatientTaskService)->patientTaskUpdate($request,$id , $patientTaskId);
    }

    // Delete Patient Task
    public function deletePatientTask(Request $request , $id, $patientTaskId)
    {
        return (new PatientTaskService)->deletePatientTask($request,$id , $patientTaskId);
    }
}
