<?php

namespace App\Services\Api;

use App\Helper;
use Exception;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\Auth;
use App\Models\ErrorLog\ErrorLogWithDeviceInfo;

class ErrorLogService
{

    // Add TimeLog
    public function errorLogWithDeviceInfoAdd($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $postData = $request->all();
            $insertData = array();
            if (isset(auth()->user()->id)) {
                $insertData["userId"] = auth()->user()->id;
            }
            if (isset($postData["deviceInfo"]) && !empty($postData["deviceInfo"])) {
                $insertData["deviceInfo"] = $postData["deviceInfo"];
            } else {
                return response()->json(['message' => "deviceInfo is Required"], 500);
            }
            if (isset($postData["errorMessage"]) && !empty($postData["errorMessage"])) {
                $insertData["errorMessage"] = $postData["errorMessage"];
            } else {
                return response()->json(['message' => "errorMessage is Required"], 500);
            }
            $udid = Str::uuid()->toString();
            $insertData["udid"] = $udid;
            $insertData["providerId"] = $provider;
            $insertData["providerLocationId"] = $providerLocation;
            $insertData["entityType"] = $entityType;
            $error = ErrorLogWithDeviceInfo::create($insertData);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'errorLogWithDeviceInfo', 'tableId' => $error->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($insertData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messaege.createdSuccesfully')], 200);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
