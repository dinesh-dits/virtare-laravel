<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Helper;
use App\Models\Blackbox;
use App\Models\Flag\Flag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Log\ChangeLog;
use App\Models\Vital\VitalField;
use Illuminate\Support\Facades\DB;
use App\Models\Patient\PatientFlag;
use App\Http\Controllers\Controller;
use App\Models\Patient\PatientVital;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalCode\GlobalCode;
use App\Models\CPTCode\CptCodeService;
use App\Models\Patient\PatientTimeLine;
use App\Models\Patient\PatientInventory;
use App\Transformers\BlackboxTransformer;
use App\Services\Api\CptCodeServiceDetailService;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Services\Api\CPTCodeService as CPTCodeServiceClass;

class WebhookController extends Controller
{
    // Blackbox Vital Upload
    public function addBlackbox(Request $request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            if (isset($request->values)) {
                $mac = trim($request->imei);
                $inventory = PatientInventory::whereHas('inventory', function ($query) use ($mac) {
                    $query->where('macAddress', $mac);
                })->first();
                if ($inventory) {
                    $vitalType = array();
                    $vitalValue = array();
                    $vitalUnits = array();
                    $valuesArray = array();
                    $vitalFlag = array();
                    $valuesArray['systolic'] = $request->values['systolic'];
                    $valuesArray['diastolic'] = $request->values['diastolic'];
                    $valuesArray['pulse'] = $request->values['pulse'];
                    $valuesArray['irregular'] = $request->values['irregular'];
                    foreach ($valuesArray as $index => $value) {
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
                            $deviceType = $inventory->inventory->model->deviceTypeId;
                            $cptService = CPTCodeService::where('patientId', $inventory->patientId)->where('referenceId', $deviceType)->where('entity', 'device')->first();
                            if (!$cptService) {
                                $cpt = new CptCodeServiceDetailService;
                                $inputData = [
                                    'referenceId' => $inventory->inventory->model->deviceTypeId, 'patientId' => $inventory->patientId,
                                    'serviceId' => $inventory->patientId, 'providerId' => $providerId, 'placeOfService' => @$inventory->patient->placeOfServiceId
                                ];
                                $cpt->cptCode($inputData);
                                //CPTCodeServiceClass::processNextBillingDetail($request);
                                //CPTCodeServiceClass::insertNextBillingServiceDetail($request);
                            }
                            $patientIdx = $inventory->patientId;
                            $data = [
                                'vitalFieldId' => $field->id,
                                'deviceTypeId' => $inventory->inventory->model->deviceTypeId,
                                'createdBy' => Auth::id(),
                                'udid' => Str::uuid()->toString(),
                                'value' => $value,
                                'patientId' => $patientIdx,
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
                            if ($vitalState) {
                                $data['flagId'] = $vitalState[0]->flagId;
                                array_push($vitalFlag, $vitalState[0]->flagId);
                            }
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
                    if (in_array("7", $vitalFlag)) {
                        $flagId = 7;
                    } elseif (in_array("8", $vitalFlag)) {
                        $flagId = 8;
                    } else {
                        $flagId = 9;
                    }

                    // Add Flag into patientFlag Table

                    $flagData = PatientFlag::where('patientId', $patientIdx)->first();

                    if ($flagData) {
                        $flagOld = Flag::where('id', $flagData->flagId)->first();
                        $flags = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
                        PatientFlag::where('patientId', $patientIdx)->update($flags);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'patientFlags', 'tableId' => $flagData->id,
                            'value' => json_encode($flags), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                        PatientFlag::where('patientId', $patientIdx)->delete();
                        $flagDataInput = ['udid' => Str::uuid()->toString(), 'patientId' => $patientIdx, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                        $flag = PatientFlag::create($flagDataInput);
                        $flagInput = Flag::where('id', $flagId)->first();
                        $flagTimeline = [
                            'patientId' => $patientIdx, 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Changed ' . $flagOld->name . ' -> ' . $flagInput->name . ' ' . '<b>By Blackbox reading</b>', 'type' => 7,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                        ];
                        PatientTimeLine::create($flagTimeline);
                    } else {
                        $flagData = ['udid' => Str::uuid()->toString(), 'patientId' => $patientIdx, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                        $flag = PatientFlag::create($flagData);
                        $flagInput = Flag::where('id', $flagId)->first();
                        $flagTimeline = [
                            'patientId' => $patientIdx, 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Added ' . $flagInput->name . ' ' . '<b>By Blackbox reading</b>', 'type' => 7,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                        ];
                        PatientTimeLine::create($flagTimeline);
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
                    $vitalFlag = array();
                    foreach ($request->value as $index => $value) {
                        if (!isset($vitalType[$request->recorded_at])) {
                            $vitalType[$request->recorded_at] = array();
                            $vitalValue[$request->recorded_at] = array();
                            $vitalUnits[$request->recorded_at] = array();
                        }
                        if ($value == '') {
                            continue;
                        }
                        if ($index == 'pulse_rate_bpm' || $index == 'pulse_bpm') {
                            $index = 'BPM';
                        } elseif ($index == 'spo2_percentage') {
                            $index = 'SPO2';
                        }
                        $field = VitalField::where('name', $index)->first();
                        if ($field) {
                            $deviceType = $inventory->inventory->model->deviceTypeId;
                            $cptService = CPTCodeService::where('patientId', $inventory->patientId)->where('referenceId', $deviceType)->where('entity', 'device')->first();
                            if (!$cptService) {
                                $cpt = new CptCodeServiceDetailService;
                                $inputData = [
                                    'referenceId' => $inventory->inventory->model->deviceTypeId, 'patientId' => $inventory->patientId,
                                    'serviceId' => $inventory->patientId, 'providerId' => $providerId, 'placeOfService' => $inventory->patient->placeOfServiceId
                                ];
                                $cpt->cptCode($inputData);
                                //CPTCodeServiceClass::processNextBillingDetail($request);
                                //CPTCodeServiceClass::insertNextBillingServiceDetail($request);
                            }
                            $patientIdx = $inventory->patientId;
                            $data = [
                                'vitalFieldId' => $field->id,
                                'deviceTypeId' => $inventory->inventory->model->deviceTypeId,
                                'createdBy' => Auth::id(),
                                'udid' => Str::uuid()->toString(),
                                'value' => $value,
                                'patientId' => $patientIdx,
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
                            if ($vitalState) {
                                $data['flagId'] = $vitalState[0]->flagId;
                                array_push($vitalFlag, $vitalState[0]->flagId);
                            }
                            $vitalData = PatientVital::create($data);
                            $device = GlobalCode::where('id', $inventory->inventory->model->deviceTypeId)->first();
                            if ($inventory->inventory->model->deviceTypeId == 99 || $inventory->inventory->model->deviceTypeId == 100 || $inventory->inventory->model->deviceTypeId == 101) {
                                $typeTimeline = 4;
                                $timeLineHeading = "Vital Uploaded";
                            } else {
                                $typeTimeline = 10;
                                $timeLineHeading = "Health Data Added ";
                            }
                            $data_vitalType[$request->recorded_at][$field->name] = $field->name;
                            $data_vitalValue[$request->recorded_at][$field->name] = $value;
                            $data_vitalUnits[$request->recorded_at][$field->name] = $field->units;
                            //  array_push($vitalType[$request->recorded_at], $field->name);
                            // array_push($vitalValue[$request->recorded_at], $data_vitalValue);
                            // array_push($vitalUnits[$request->recorded_at],$data_vitalUnits);
                        }
                    }

                    if (in_array("7", $vitalFlag)) {
                        $flagId = 7;
                    } elseif (in_array("8", $vitalFlag)) {
                        $flagId = 8;
                    } else {
                        $flagId = 9;
                    }
                    // Add Flag into patientFlag Table
                    $flagData = PatientFlag::where('patientId', $patientIdx)->first();

                    if ($flagData) {
                        $flagOld = Flag::where('id', $flagData->flagId)->first();
                        $flags = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
                        PatientFlag::where('patientId', $patientIdx)->update($flags);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'patientFlags', 'tableId' => $flagData->id,
                            'value' => json_encode($flags), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                        PatientFlag::where('patientId', $patientIdx)->delete();
                        $flagDataInput = ['udid' => Str::uuid()->toString(), 'patientId' => $patientIdx, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                        $flag = PatientFlag::create($flagDataInput);
                        $flagInput = Flag::where('id', $flagId)->first();

                        $flagTimeline = [
                            'patientId' => $patientIdx, 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Changed ' . $flagOld->name . ' -> ' . $flagInput->name . ' ' . '<b>By Blackbox reading</b>', 'type' => 7,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                        ];
                        PatientTimeLine::create($flagTimeline);
                    } else {
                        $flagData = ['udid' => Str::uuid()->toString(), 'patientId' => $patientIdx, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                        $flag = PatientFlag::create($flagData);
                        $flagInput = Flag::where('id', $flagId)->first();
                        $flagTimeline = [
                            'patientId' => $patientIdx, 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Addded ' . $flagInput->name . ' ' . '<b>By Blackbox reading</b>', 'type' => 7,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                        ];
                        PatientTimeLine::create($flagTimeline);
                    }
                    $vitalTypeArray[$request->recorded_at]['Systolic'] = 'Systolic';
                    $vitalTypeArray[$request->recorded_at]['Diastolic'] = 'Diastolic';
                    $vitalTypeArray[$request->recorded_at]['BPM'] = 'BPM';

                    foreach ($vitalTypeArray as $time => $val) {
                        $vitalStr = "";
                        foreach ($val as $index => $vital) {
                            $vitalStr .= $data_vitalType[$time][$index] . " " . $data_vitalValue[$time][$index] . " " . $data_vitalUnits[$time][$index] . " " . ",";
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
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage() . '++' . $e->getLine() . '+++' . $e->getFile()],  500);
        }
    }

    // List Blackbox
    public function getBlackbox(Request $request)
    {
        $blackBox = Blackbox::select("udid", "vital", "value", "takeTime", "requestString as request")->orderBy('createdAt', "DESC")->paginate(env('PER_PAGE', 20));
        return fractal()->collection($blackBox)->transformWith(new BlackboxTransformer())->paginateWith(new IlluminatePaginatorAdapter($blackBox))->toArray();
    }
}
