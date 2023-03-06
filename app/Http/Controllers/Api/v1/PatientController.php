<?php

namespace App\Http\Controllers\Api\v1;

use \App\Library\BitrixApi;
use Illuminate\Http\Request;
use App\Services\Api\FamilyService;
use App\Http\Controllers\Controller;
use App\Services\Api\PatientService;
use App\Http\Requests\Family\FamilyRequest;
use App\Services\Api\ExcelGeneratorService;
use App\Http\Requests\Patient\PatientRequest;
use App\Http\Requests\Patient\PhysicianRequest;
use App\Services\Api\ExportReportRequestService;
use App\Http\Requests\Patient\PatientProgramRequest;
use App\Http\Requests\Patient\PatientReferalRequest;
use App\Http\Requests\Patient\PatientConditionRequest;
use App\Http\Requests\Patient\PatientEmergencyRequest;
use App\Http\Requests\Patient\PatientResponsibleRequest;
use App\Http\Requests\Patient\PatientCriticalNoteRequest;
use App\Http\Requests\Patient\PatientFamilymemberRequest;
use App\Http\Requests\Patient\PatientMedicalHistoryRequest;
use App\Http\Requests\Patient\PatientMedicalRoutineRequest;
use App\Models\Patient\Patient;


class PatientController extends Controller
{

  // Add Patient
  public function createPatient(PatientRequest $request, $id = null)
  {
    return (new PatientService)->patientCreate($request, $id);
  }

  // List Patient
  public function listPatient(Request $request, $id = null)
  {
    return (new PatientService)->patientList($request, $id);
  }

  // Delete Patient
  public function deletePatient(Request $request, $id)
  {
    return (new PatientService)->patientDelete($request, $id);
  }

  // Update Patient Status
  public function updatePatientStatus(Request $request, $id)
  {
    return (new PatientService)->patientUpdateStatus($request, $id);
  }

  // Reset Password
  public function resetPassword(Request $request, $id)
  {
    return (new PatientService)->passwordReset($request, $id);
  }

  // Add Patient Condition
  public function createPatientCondition(PatientConditionRequest $request, $id, $conditionId = null)
  {
    return (new PatientService)->patientConditionCreate($request, $id, $conditionId);
  }

  // Delete Patient Condition
  public function deletePatientCondition(Request $request, $id, $conditionId)
  {
    return (new PatientService)->patientConditionDelete($request, $id, $conditionId);
  }

  // List Patient Condition
  public function listPatientCondition(Request $request, $id, $conditionId = null)
  {
    return (new PatientService)->patientConditionList($request, $id, $conditionId);
  }

  // Add Patient Referal
  public function createPatientReferals(PatientReferalRequest $request, $id)
  {
    return (new PatientService)->patientReferalsCreate($request, $id);
  }

  // Update Patient Referals
  public function updatePatientReferals(PatientReferalRequest $request, $id, $referalsId)
  {
    return (new PatientService)->patientReferalsUpdate($request, $id, $referalsId);
  }

  // List Patient Referals
  public function listPatientReferral(Request $request, $id)
  {
    return (new PatientService)->listPatientReferral($request, $id);
  }

  // Referal List
  public function referral(Request $request)
  {
    return (new PatientService)->referral($request);
  }

  // Delete Patient Referals
  public function deletePatientReferals(Request $request, $id, $referalsId)
  {
    return (new PatientService)->patientReferalsDelete($request, $id, $referalsId);
  }

  // Add Patient Physician
  public function createPatientPhysician(PhysicianRequest $request, $id, $physicianId = null)
  {
    return (new PatientService)->patientPhysicianCreate($request, $id, $physicianId);
  }

  // Update Patient Physician
  public function updatePatientPhysician(PhysicianRequest $request, $id, $physicianId = null)
  {
    return (new PatientService)->patientPhysicianCreate($request, $id, $physicianId);
  }

  // List Patient Physician
  public function listPatientPhysician(Request $request, $id, $physicianId = null)
  {
    return (new PatientService)->patientPhysicianList($request, $id, $physicianId);
  }

  // Delete Patient Physician
  public function deletePatientPhysician(Request $request, $id, $physicianId)
  {
    return (new PatientService)->patientPhysicianDelete($request, $id, $physicianId);
  }

  // Add Patient Program
  public function createPatientProgram(PatientProgramRequest $request, $id, $programId = null)
  {
    return (new PatientService)->patientProgramCreate($request, $id, $programId);
  }
  
  // Update Patient Program
  public function updatePatientProgram(PatientProgramRequest $request, $id, $programId = null)
  {
    return (new PatientService)->patientProgramUpdate($request, $id, $programId);
  }

  // List Patient Program
  public function listPatientProgram(Request $request, $id, $programId = null)
  {
    return (new PatientService)->patientProgramList($request, $id, $programId);
  }

  // // Update Patient Program
  // public function updatePatientProgram(PatientProgramRequest $request, $id, $programId = null)
  // {
  //   return (new PatientService)->patientProgramUpdate($request, $id, $programId);
  // }
  
  // Delete Patient Program
  public function deletePatientProgram(Request $request, $id, $programId = null)
  {
    return (new PatientService)->patientProgramDelete($request, $id, $programId);
  }

  // Add Patient Inventory
  public function createPatientInventory(Request $request, $id, $inventoryId = null)
  {
    return (new PatientService)->patientInventoryCreate($request, $id, $inventoryId);
  }

  // Update Patient Inventory
  public function updatePatientInventory(Request $request, $id, $inventoryId = null)
  {
    return (new PatientService)->patientInventoryCreate($request, $id, $inventoryId);
  }

  // List Patient Inventory
  public function listPatientInventory(Request $request, $id, $inventoryId = null)
  {
    return (new PatientService)->patientInventoryList($request, $id, $inventoryId);
  }

  // Delete Patient Inventory
  public function deletePatientInventory(Request $request, $id, $inventoryId)
  {
    return (new PatientService)->patientInventoryDelete($request, $id, $inventoryId);
  }

  // Add Patient Vital
  public function createPatientVital(Request $request, $id = null)
  {
    return (new PatientService)->patientVitalCreate($request, $id);
  }

  // List Patient Vital
  public function listPatientVital(Request $request, $id = null)
  {
    return (new PatientService)->patientVitalList($request, $id);
  }

  // Vital 
  public function vital(Request $request, $id = null)
  {
    return (new PatientService)->vitalList($request, $id);
  }

  // Vitals Latest
  public function latest(Request $request, $id = null, $vitalType = null)
  {
    return (new PatientService)->latest($request, $id, $vitalType);
  }

  // Delete Patient Vitals
  public function deletePatientVital(Request $request, $id, $vitalId = null)
  {
    return (new PatientService)->patientVitalDelete($request, $id, $vitalId);
  }

  // Add Patient Medical History
  public function createPatientMedicalHistory(PatientMedicalHistoryRequest $request, $id, $medicalHistoryId = null)
  {
    return (new PatientService)->patientMedicalHistoryCreate($request, $id, $medicalHistoryId);
  }

  // List Patient Medical History
  public function listPatientMedicalHistory(Request $request, $id, $medicalHistoryId = null)
  {
    return (new PatientService)->patientMedicalHistoryList($request, $id, $medicalHistoryId);
  }

  // Delete Patient Medical History
  public function deletePatientMedicalHistory(Request $request, $id, $medicalHistoryId)
  {
    return (new PatientService)->patientMedicalHistoryDelete($request, $id, $medicalHistoryId);
  }

  // Add Patient Medical Routine
  public function createPatientMedicalRoutine(PatientMedicalRoutineRequest $request, $id, $medicalRoutineId = null)
  {
    return (new PatientService)->patientMedicalRoutineCreate($request, $id, $medicalRoutineId);
  }

  // List Patient Medical Routine
  public function listPatientMedicalRoutine(Request $request, $id, $medicalRoutineId = null)
  {
    return (new PatientService)->patientMedicalRoutineList($request, $id, $medicalRoutineId);
  }

  // Delete Patient Medical Routine
  public function deletePatientMedicalRoutine(Request $request, $id, $medicalRoutineId)
  {
    return (new PatientService)->patientMedicalRoutineDelete($request, $id, $medicalRoutineId);
  }

  // Add Patient Insurance
  public function createPatientInsurance(Request $request, $id, $insuranceId = null)
  {
    return (new PatientService)->patientInsuranceCreate($request, $id, $insuranceId);
  }

  // List Patient Insurance
  public function listPatientInsurance(Request $request, $id, $insuranceId = null)
  {
    return (new PatientService)->patientInsuranceList($request, $id, $insuranceId);
  }

  // Delete Patient Insurance
  public function deletePatientInsurance(Request $request, $id, $insuranceId)
  {
    return (new PatientService)->patientInsuranceDelete($request, $id, $insuranceId);
  }

  // List Patient Inventory
  public function listingPatientInventory(Request $request, $id = null)
  {
    return (new PatientService)->patientInventoryListing($request, $id);
  }

  // Update Inventory
  public function inventory(Request $request, $id)
  {
    return (new PatientService)->inventoryUpdate($request, $id);
  }

  // Add Patient Device
  public function createPatientDevice(Request $request, $id = null, $deviceId = null)
  {
    return (new PatientService)->patientDeviceCreate($request, $id, $deviceId);
  }

  // List Patient Device
  public function listPatientDevice(Request $request, $id = null, $deviceId = null)
  {
    return (new PatientService)->patientDeviceList($request, $id, $deviceId);
  }

  // List Patient Timeline
  public function listPatientTimeline(Request $request, $id = null)
  {
    return (new PatientService)->patientTimelineList($request, $id);
  }

  // Add Patient Flag
  public function addPatientFlag(Request $request, $id = null)
  {
    return (new PatientService)->patientFlagAdd($request, $id);
  }

  // List Patient Flag
  public function listPatientFlag(Request $request, $id = null, $flagId = null)
  {
    return (new PatientService)->patientFlagList($request, $id, $flagId);
  }

  // Delete Patient Flag
  public function deletePatientFlag(Request $request, $id, $flagId)
  {
    return (new PatientService)->patientFlagDelete($request, $id, $flagId);
  }

  // List Patient Critical Note
  public function listPatientCriticalNote(Request $request, $id = null, $noteId = null)
  {
    return (new PatientService)->listPatientCriticalNote($request, $id, $noteId);
  }

  // Add Patient Critical Note
  public function createPatientCriticalNote(PatientCriticalNoteRequest $request, $id)
  {
    return (new PatientService)->createPatientCriticalNote($request, $id);
  }

  // Update Patient Critical Note
  public function updatePatientCriticalNote(Request $request, $id, $noteId)
  {
    return (new PatientService)->updatePatientCriticalNote($request, $id, $noteId);
  }

  // Delete Patient Critical Note
  public function deletePatientCriticalNote(Request $request, $id, $noteId)
  {
    return (new PatientService)->deletePatientCriticalNote($request, $id, $noteId);
  }

  // Add Patient Family
  public function addPatientFamily(PatientFamilymemberRequest $request, $id, $familyId = null)
  {
    return (new PatientService)->patientFamilyAdd($request, $id, $familyId);
  }

  // List Patient Family
  public function listPatientFamily(Request $request, $id, $familyId = null)
  {
    return (new PatientService)->patientFamilyList($request, $id, $familyId);
  }

  // Delete Patient Family
  public function deletePatientFamily(Request $request, $id, $familyId = null)
  {
    return (new PatientService)->patientFamilyDelete($request, $id, $familyId);
  }

  // List Patient Family Member
  public function patientPhycisian(Request $request, $id)
  {
    return (new PatientService)->phycisianPatient($request, $id);
  }

  // Add Patient Emergency
  public function addPatientEmergency(Request $request, $id, $emergencyId = null)
  {
    return (new PatientService)->patientEmergencyAdd($request, $id, $emergencyId);
  }

  // List Patient Emergency
  public function listPatientEmergency(Request $request, $id, $emergencyId = null)
  {
    return (new PatientService)->patientEmergencyList($request, $id, $emergencyId);
  }

  // Delete Patient Emergency
  public function deletePatientEmergency(Request $request, $id, $emergencyId = null)
  {
    return (new PatientService)->patientEmergencyDelete($request, $id, $emergencyId);
  }

  // Upadte Profile
  public function updateProfile(Request $request, $id)
  {
    return (new PatientService)->profileUpdate($request, $id);
  }

  public function patientProviderUpdate(Request $request, $id)
  {
    return (new PatientService)->patientProviderUpdate($request, $id);
  }

  public function addPatientGroup(Request $request, $id)
  {
    return (new PatientService)->patientGroupAdd($request, $id);
  }

  public function listPatientGroup(Request $request, $id)
  {
    return (new PatientService)->patientGroupList($request, $id);
  }









  // Family 
  public function createFamily(FamilyRequest $request, $id, $familyId = null)
  {
    return (new FamilyService)->familyCreate($request, $id, $familyId);
  }


  // Bitrix APi for getting single deal
  public function getBitrixDealById(Request $request, $patientId)
  {
    if ($patientId) {

      // get deal from the bitrix24 api
      $response = BitrixApi::getDealById($patientId);
      return response()->json($response, 200);
    } else {

      $json = array(
        "error" => "Patient ID is Required."
      );

      return json_encode($json);
    }
  }


  // Bitrix APi for list all deals
  public function getAllBitrixDeals(Request $request, $patientId = null)
  {
    // get deal from the bitrix24 api
    $data = $request->all();
    if ($patientId) {
      // get deal from the bitrix24 api
      $response = BitrixApi::getDealById($patientId);
      return response()->json($response, 200);
    } else if (isset($data["title"])) {
      // get deal by name from the bitrix24 api
      $response = BitrixApi::getDealByName($data["title"]);
      return response()->json($response, 200);
    } else {
      $response = BitrixApi::getAllDeal();
      return response()->json($response, 200);
    }
  }

  // Patient Report
  public function patientReport(Request $request, $id)
  {
    if ($id) {
      $reportType = "patient_report";
      $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
      if ($checkReport) {
        ExcelGeneratorService::patientExcelExport($request, $id);
      } else {
        return response()->json(['message' => "User not Access to download Report."], 403);
      }
    } else {
      return response()->json(['message' => "invalid URL."], 400);
    }
  }

  // Patient Report
  public function referralReport(Request $request, $id)
  {
    if ($id) {
      $reportType = "referral_report";
      $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
      if ($checkReport) {
        ExcelGeneratorService::referralExcelExport($request, $id);
      } else {
        return response()->json(['message' => "User not Access to download Report."], 403);
      }
    } else {
      return response()->json(['message' => "invalid URL."], 400);
    }
  }

  // Patient Report
  public function patientVitalReport(Request $request, $id)
  {
    if ($id) {
      $reportType = "patientVital_report";
      $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
      if ($checkReport) {
        ExcelGeneratorService::vitalExcelExport($request, $id);
      } else {
        return response()->json(['message' => "User not Access to download Report."], 403);
      }
    } else {
      return response()->json(['message' => "invalid URL."], 400);
    }
  }

  // List Patient Timeline Type
  public function listPatientTimeLineType(Request $request)
  {
    return (new PatientService)->patientTimeLineTypeList($request);
  }

  // Add patient Responsible
  public function patientResponsible(PatientResponsibleRequest $request, $id, $responsibleId = null)
  {
    return (new PatientService)->responsiblePatient($request, $id, $responsibleId);
  }

  // List Patient Responsible
  public function listPatientResponsible(Request $request, $id, $responsibleId = null)
  {
    return (new PatientService)->listResponsiblePatient($request, $id, $responsibleId);
  }

  // Add Patient Flags
  public function addPatientFlags(Request $request, $id = null)
  {
    return (new PatientService)->addPatientFlag($request, $id);
  }

  public function encryptdata(){
    $patients = Patient::all();
    
  }
}
