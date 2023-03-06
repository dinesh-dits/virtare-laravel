<?php

namespace App\Http\Controllers\Api\v1;

use App\Helper;
use App\Models\Blackbox;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Log\ChangeLog;
use App\Models\Vital\VitalField;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Patient\PatientVital;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\PatientTimeLine;
use App\Models\Patient\PatientInventory;
use App\Transformers\BlackboxTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class WebhookNewController extends Controller
{
    public function addBlackbox(Request $request)
    {
        $providerId = Helper::providerId();
        if (isset($request->values)) {
            $inventory = PatientInventory::whereHas('inventory', function ($query) use ($request) {
                $query->where('macAddress', $request->imei);
            })->first();
            if ($inventory) {
                $vitalType = array();
                $vitalValue = array();
                $vitalUnits = array();
                foreach ($request->values as $index => $value) {
                    if (!isset($vitalType[$request->recorded_at])) {
                        $vitalType[$request->recorded_at] = array();
                        $vitalValue[$request->recorded_at] = array();
                        $vitalUnits[$request->recorded_at] = array();
                    }
                    if ($value == '') {
                        continue;
                    }
                    if ($index == 'pulse') {
                        $index = 'BPM';
                    }
                    $field = VitalField::where('name', $index)->first();
                    if ($field) {
                        $data = [
                            'vitalFieldId' => $field->id,
                            'deviceTypeId' => $inventory->inventory->model->deviceTypeId,
                            'createdBy' => Auth::id(),
                            'udid' => Str::uuid()->toString(),
                            'value' => $value,
                            'patientId' => $inventory->patientId,
                            'units' => $field->units,
                            'takeTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                            'startTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                            'endTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                            'providerId' => $providerId,
                            'addType' => 'Sync',
                            'deviceInfo' => 'API',
                            'createdType' => 'self',
                        ];
                        $vitalState = DB::select(
                            'CALL vitalRangeFlag("' . $field->id . '","' . $value . '")',
                        );
                        $data['flagId'] = $vitalState[0]->vitalFlagId;
                        $vitalData = PatientVital::create($data);
                        $device = GlobalCode::where('id', $inventory->inventory->model->deviceTypeId)->first();
                        if ($inventory->inventory->model->deviceTypeId == 99 || $inventory->inventory->model->deviceTypeId == 100 || $inventory->inventory->model->deviceTypeId == 101) {
                            $typeTimeline = 4;
                            $timeLineHeading = "Vital Uploaded";
                        } else {
                            $typeTimeline = 10;
                            $timeLineHeading = "Health Data Added ";
                        }
                        array_push($vitalType[$request->recorded_at], $field->name);
                        array_push($vitalValue[$request->recorded_at], $value);
                        array_push($vitalUnits[$request->recorded_at], $field->units);
                    }
                }
                foreach ($vitalType as $time => $val) {
                    $vitalStr = "";
                    foreach ($val as $index => $vital) {
                        $vitalStr .= $vitalType[$time][$index] . " " . $vitalValue[$time][$index] . " " . $vitalUnits[$time][$index] . " " . ",";
                    }
                    $vitalStr = rtrim($vitalStr, ',');
                    $timeLine = [
                        'patientId' => $inventory->patientId, 'heading' => $timeLineHeading, 'title' => $device->name . ' ' . 'Reading:' . ' ' . $vitalStr, 'type' => $typeTimeline,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'refrenceId' => $vitalData->id
                    ];
                    $timeline = PatientTimeLine::create($timeLine);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id,
                        'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId
                    ];
                    ChangeLog::create($changeLog);
                }
            } else {
                $input = [
                    'udid' => Str::uuid()->toString(),
                    'vital' => $request->measurement_type,
                    'value' => json_encode($request->values),
                    'takeTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                    'syncTime' => date("Y-m-d H:i:s"),
                    'requestString' => json_encode($request->all()),
                ];
                $data = Blackbox::create($input);
            }
        } else {
            $inventory = PatientInventory::whereHas('inventory', function ($query) use ($request) {
                $query->where('macAddress', $request->device_serial_number);
            })->first();
            if ($inventory) {
                $vitalType = array();
                $vitalValue = array();
                $vitalUnits = array();
                foreach ($request->value as $index => $value) {
                    if (!isset($vitalType[$request->recorded_at])) {
                        $vitalType[$request->recorded_at] = array();
                        $vitalValue[$request->recorded_at] = array();
                        $vitalUnits[$request->recorded_at] = array();
                    }
                    if ($value == '') {
                        continue;
                    }
                    if ($index == 'pulse_rate_bpm') {
                        $index = 'BPM';
                    } elseif ($index == 'spo2_percentage') {
                        $index = 'SPO2';
                    }
                    $field = VitalField::where('name', $index)->first();
                    if ($field) {
                        $data = [
                            'vitalFieldId' => $field->id,
                            'deviceTypeId' => $inventory->inventory->model->deviceTypeId,
                            'createdBy' => Auth::id(),
                            'udid' => Str::uuid()->toString(),
                            'value' => $value,
                            'patientId' => $inventory->patientId,
                            'units' => $field->units,
                            'takeTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                            'startTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                            'endTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                            'providerId' => $providerId,
                            'addType' => 'Sync',
                            'deviceInfo' => 'API',
                            'createdType' => 'self',
                        ];
                        $vitalState = DB::select(
                            'CALL vitalRangeFlag("' . $field->id . '","' . $value . '")',
                        );
                        $data['flagId'] = $vitalState[0]->vitalFlagId;
                        $vitalData = PatientVital::create($data);
                        $device = GlobalCode::where('id', $inventory->inventory->model->deviceTypeId)->first();
                        if ($inventory->inventory->model->deviceTypeId == 99 || $inventory->inventory->model->deviceTypeId == 100 || $inventory->inventory->model->deviceTypeId == 101) {
                            $typeTimeline = 4;
                            $timeLineHeading = "Vital Uploaded";
                        } else {
                            $typeTimeline = 10;
                            $timeLineHeading = "Health Data Added ";
                        }
                        array_push($vitalType[$request->recorded_at], $field->name);
                        array_push($vitalValue[$request->recorded_at], $value);
                        array_push($vitalUnits[$request->recorded_at], $field->units);
                    }
                }
                foreach ($vitalType as $time => $val) {
                    $vitalStr = "";
                    foreach ($val as $index => $vital) {
                        $vitalStr .= $vitalType[$time][$index] . " " . $vitalValue[$time][$index] . " " . $vitalUnits[$time][$index] . " " . ",";
                    }
                    $vitalStr = rtrim($vitalStr, ',');
                    $timeLine = [
                        'patientId' => $inventory->patientId, 'heading' => $timeLineHeading, 'title' => $device->name . ' ' . 'Reading:' . ' ' . $vitalStr, 'type' => $typeTimeline,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'refrenceId' => $vitalData->id
                    ];
                    $timeline = PatientTimeLine::create($timeLine);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id,
                        'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId
                    ];
                    ChangeLog::create($changeLog);
                }
            } else {
                $input = [
                    'udid' => Str::uuid()->toString(),
                    'vital' => $request->measurement_type,
                    'value' => json_encode($request->value),
                    'takeTime' => date("Y-m-d H:i:s", strtotime($request->recorded_at)),
                    'syncTime' => date("Y-m-d H:i:s"),
                    'requestString' => json_encode($request->all()),
                ];
                $data = Blackbox::create($input);
            }
        }
        return response()->json(['message' => trans('messages.createdSuccesfully')],  200);
    }


    public function getBlackbox(Request $request)
    {
        $blackBox = Blackbox::select("udid", "vital", "value", "takeTime", "requestString as request")->orderBy('createdAt', "DESC")->paginate(env('PER_PAGE', 20));
        return fractal()->collection($blackBox)->transformWith(new BlackboxTransformer())->paginateWith(new IlluminatePaginatorAdapter($blackBox))->toArray();
    }
}
