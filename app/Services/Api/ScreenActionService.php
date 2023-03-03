<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Helper as AppHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ScreenActionService
{
    // Add Screen Action
    public function addScreenAction($request)
    {
        try {
            $userId = $request->userId;
            $actionId = $request->actionId;
            $deviceId = $request->deviceId;
            $provider = Helper::providerId();
            DB::select('CALL createScreenAction(' . $provider . ',' . $userId . ',' . $actionId . ',' . $deviceId . ')');
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Screen Action
    public function addPatientScreenAction($request, $id)
    {
        try {
            $patient = AppHelper::entity('patient', $id);
            $udid = Str::uuid()->toString();
            $actionId = $request->input('actionId');
            $userid = Auth::id();
            $provider = Helper::providerId();
            DB::select('CALL createPatientScreenAction("' . $provider . '","' . $udid . '","' . $userid . '","' . $patient . '","' . $actionId . '")');
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
