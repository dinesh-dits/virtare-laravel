<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\Auth;
use App\Models\CPTCode\CptCodeService;
use App\Models\CPTCode\CptCodeActivity;
use App\Models\Patient\PatientCondition;
use App\Models\CPTCode\CptCodeServiceDetail;
use App\Models\CPTCode\CptCodeServiceCondition;
use App\Models\CPTCode\CptCodeNextBillingServices;


class CptCodeServiceDetailService
{
    // Add CPT Code Service
    public function cptCode($data)
    {
        try {
            $condition = PatientCondition::where([['patientId', $data['patientId']], ['endDate', '>', Carbon::now()]])->whereNull('deletedAt')->get();
            $cptActivity = CptCodeActivity::where('id', 1)->first();
            $cptData = [
                'udid' => Str::uuid()->toString(), 'cptCodeActivityId' => 1, 'patientId' => $data['patientId'], 'entity' => 'device',
                'referenceId' => $data['referenceId'], 'units' => 1, 'cost' => $cptActivity->cptCode->billingAmout,
                'status' => 297, 'placeOfService' => $data['placeOfService'], 'createdBy' => Auth::id(), 'providerId' => $data['providerId']
            ];
            $cpt = CptCodeService::create($cptData);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'cptCodeServices', 'tableId' => $cpt->id,
                'value' => json_encode($cptData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $data['providerId']
            ];
            ChangeLog::create($changeLog);

            // insert next billing data
            $cptActivity = CptCodeActivity::where('id', 2)->first();
            if (isset($cptActivity->billingInterval)) {
                $billingInterval = $cptActivity->billingInterval;
            } else {
                $billingInterval = 30;
            }
            $lastDate = $cpt->createdAt;
            $nextDate = strtotime($cpt->createdAt . ' + ' . $billingInterval . ' days');
            $nextDate = Helper::date($nextDate);

            $insertArr = array(
                "referenceId" => $cpt->id,
                "entityType" => "cptCodeServices",
                "functionName" => "inventory",
                "controllerName" => "PatientController",
                "lastBillingAt" => $lastDate,
                "nextBillingAt" => $nextDate,
                'providerId' => $data['providerId'],
                'createdBy' => Auth::id()
            );
            $cptBilling = CptCodeNextBillingServices::create($insertArr);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'cptCodeNextBillingServices', 'tableId' => $cptBilling->id,
                'value' => json_encode($insertArr), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $data['providerId']
            ];
            ChangeLog::create($changeLog);

            $cptData = [
                'udid' => Str::uuid()->toString(), 'cptCodeServicesId' => $cpt->id, 'entity' => 'device',
                'serviceId' => $data['serviceId'], 'createdBy' => Auth::id(), 'providerId' => $data['providerId']
            ];
            $cptDetail = CptCodeServiceDetail::create($cptData);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'cptCodeServicesDetail', 'tableId' => $cptDetail->id,
                'value' => json_encode($cptDetail), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $data['providerId']
            ];
            ChangeLog::create($changeLog);
            foreach ($condition as $value) {
                $cptData = [
                    'udid' => Str::uuid()->toString(), 'serviceId' => $cpt->id, 'providerId' => $data['providerId'],
                    'conditionId' => $value['conditionId'], 'startDate' => $value['startDate'], 'endDate' => $value['endDate'], 'createdBy' => Auth::id()
                ];
                $cptDetail = CptCodeServiceCondition::create($cptData);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'cptCodeServicesDetail', 'tableId' => $cptDetail->id,
                    'value' => json_encode($cptDetail), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $data['providerId']
                ];
                ChangeLog::create($changeLog);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
