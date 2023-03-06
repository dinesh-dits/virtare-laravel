<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ProviderService;
use App\Services\Api\ExcelGeneratorService;
use App\Http\Requests\Provider\ProviderRequest;
use App\Services\Api\ExportReportRequestService;
use App\Http\Requests\Provider\ProviderContactRequest;
use App\Http\Requests\Provider\ProviderLocationRequest;

class ProviderController extends Controller
{

    // List Provider
    public function index(Request $request, $id = null)
    {
        return (new ProviderService)->index($request, $id);
    }

    public function store(ProviderRequest $request)
    {
        return (new ProviderService)->store($request);
    }

    // Add Provider Location
    public function providerLocationStore(ProviderLocationRequest $request, $id)
    {
        return (new ProviderService)->providerLocationStore($request, $id);
    }

    // List Location
    public function listLocation(Request $request, $id, $locationId = null)
    {
        return (new ProviderService)->listLocation($request, $id, $locationId);
    }

    // Update Provider
    public function updateProvider(Request $request, $id)
    {
        return (new ProviderService)->updateProvider($request, $id);
    }

    // Update Provider Location
    public function updateLocation(Request $request, $id, $locationId)
    {
        return (new ProviderService)->updateLocation($request, $id, $locationId);
    }

    // Delete Provider Location
    public function deleteProviderLocation($id, $locationId = null)
    {
        return (new ProviderService)->deleteProviderLocation($id, $locationId);
    }

    // Add Provider Location Program
    public function addProviderLocationProgram(Request $request, $id, $locationId)
    {
        return (new ProviderService)->providerLocationProgramAdd($request, $id, $locationId);
    }

    // Delete Provider Location Program
    public function deleteProviderLocationProgram(Request $request,$id, $locationId, $programId)
    {
        return (new ProviderService)->deleteProviderLocationProgram($request,$id, $locationId, $programId);
    }

    // List Provider Location Program
    public function listProviderLocationProgram(Request $request, $id, $locationId)
    {
        return (new ProviderService)->providerLocationProgramList($request, $id, $locationId);
    }

    // Provider Report
    public function providerReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "provider_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::providerExcelExport($request);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // Add Provider Contact
    public function addProviderContact(ProviderContactRequest $request, $id)
    {
        return (new ProviderService)->addProviderContact($request, $id);
    }

    // List Provider Contact
    public function listProviderContact(Request $request, $id, $contactId = null)
    {
        return (new ProviderService)->listProviderContact($request, $id, $contactId);
    }

    // Update Provider Contact
    public function updateProviderContact(ProviderContactRequest $request, $id, $contactId)
    {
        return (new ProviderService)->updateProviderContact($request, $id, $contactId);
    }

    // Add Provider Location SubLocation
    public function addProviderLocationSubLocation(Request $request, $id, $locationId)
    {
        return (new ProviderService)->providerLocationSubLocationAdd($request, $id, $locationId);
    }

    // Delete Provider Location SubLocation
    public function deleteProviderLocationSubLocation(Request $request, $id, $locationId, $subLocationId)
    {
        return (new ProviderService)->providerLocationSubLocationDelete($request, $id, $locationId, $subLocationId);
    }

    // Provider Location Update
    public function locationUpdate(Request $request, $id)
    {
        return (new ProviderService)->locationUpdate($request, $id);
    }

    // Provider Group
    public function listProviderGroup(Request $request, $id,$groupId=null)
    {
        return (new ProviderService)->providerGroupList($request, $id,$groupId);
    }
}
