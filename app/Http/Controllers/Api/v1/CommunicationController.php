<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\CommunicationService;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class CommunicationController extends Controller
{
    // Add Communication
    public function addCommunication(request $request)
    {
        return (new CommunicationService)->addCommunication($request);
    }

    // List Communication
    public function getCommunication(Request $request)
    {
        return (new CommunicationService)->getCommunication($request);
    }

    // Call Per Staff Count
    public function callCountPerStaff(request $request)
    {
        return (new CommunicationService)->callCountPerStaff($request);
    }

    // List Message Type 
    public function messageType()
    {
        return (new CommunicationService)->messageType();
    }

    // Communication Count
    public function countCommunication(Request $request)
    {
        return (new CommunicationService)->communicationCount($request);
    }

    // Communication Search
    public function searchCommunication(Request $request)
    {
        return (new CommunicationService)->communicationSearch($request);
    }

    // Update Conference Call
    public function callUpdate(Request $request, $id)
    {
        return (new CommunicationService)->updateCall($request, $id);
    }

    // Patient Call
    public function callAddPatient(Request $request)
    {
        return (new CommunicationService)->addCallPatient($request);
    }

    // UPdate Call By Staff
    public function callUpdateByStaff(Request $request, $id)
    {
        return (new CommunicationService)->updateCallByStaff($request, $id);
    }

    // Update Call By Patient
    public function callUpdateByPatient(Request $request, $id)
    {
        return (new CommunicationService)->updateCallByPatient($request, $id);
    }

    // Communication Report
    public function communicationReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "communication_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::communicationExcelExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // List Communication Message
    public function getCommunicationMessages(Request $request, $id)
    {
        return (new CommunicationService)->getCommunicationMessages($request, $id);
    }

    // List Communication Calls
    public function getCommunicationCalls(Request $request, $id)
    {
        return (new CommunicationService)->getCommunicationCalls($request, $id);
    }

    // Communication Patient Calls
    public function patientCommunicationCalls(Request $request, $id)
    {
        return (new CommunicationService)->patientCommunicationCalls($request, $id);
    }

    // Update Communication Status (Patient)
    public function patientCommunicationCallStatusUpdate(Request $request)
    {
        return (new CommunicationService)->patientCommunicationCallStatusUpdate($request);
    }

    // List Call Status
    public function callStatusList(Request $request, $id = null)
    {
        return (new CommunicationService)->callStatusList($request, $id);
    }

    // Communication Reply Message
    public function communicationReply(Request $request, $id)
    {
        return (new CommunicationService)->communicationReply($request, $id);
    }

    //Communication Inbound Config
    public function getCommunicationInbound(Request $request,$id = null)
    {
        return (new CommunicationService)->getCommunicationInbound($request,$id);
    }

    public function deleteCommunicationInbound(Request $request,$id)
    {
        return (new CommunicationService)->deleteCommunicationInbound($request,$id);
    }

    public function updateCommunicationInbound(Request $request,$id)
    {
        return (new CommunicationService)->updateCommunicationInbound($request,$id);
    }
}
