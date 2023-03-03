<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Jobs\EscaltionMailJob;
use App\Models\Referral\Referral;
use Illuminate\Support\Facades\DB;
use App\Services\Api\DomPdfService;
use Illuminate\Support\Facades\Auth;
use App\Models\Escalation\Escalation;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\PatientReferral;
use App\Models\Patient\PatientTimeLine;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification\Notification;
use App\Models\Escalation\EscalationAudit;
use App\Models\Escalation\EscalationClose;
use App\Models\ConfigMessage\ConfigMessage;
use App\Models\Escalation\EscalationAction;
use App\Models\Escalation\EscalationDetail;
use App\Models\Escalation\EscalationAssignTo;
use App\Models\Escalation\EscalationAuditDescription;
use App\Transformers\Escalation\EscalationTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Escalation\EscalationListTransformer;
use App\Transformers\Notification\NotificationTransformer;
use App\Transformers\Escalation\EscalationAuditTransformer;
use App\Transformers\Escalation\EscalationActionTransformer;
use App\Transformers\Escalation\EscalationAssignToTransformer;
use App\Events\NotificationEvent;

class EscalationService
{
    // Add Escalation
    public function addEscalation($request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $reference = Helper::tableName('App\Models\Patient\Patient', $request->input('referenceId'));
            if (isset($reference) && !empty($reference)) {
                $input = [
                    'udid' => Str::uuid()->toString(), 'typeId' => $request->input('type'), 'createdBy' => Auth::id(), 'locationEntityType' => $entityType,
                    'entityType' => $request->input('entityType'), 'referenceId' => $reference, 'reason' => $request->input('reason'), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                $lastId = Escalation::insertGetId($input);
                $newData = User::where('id', Auth::id())->first();
                $global = GlobalCode::where('id', $request->input('type'))->first();
                $timeLine = [
                    'patientId' => $reference, 'heading' => 'Escalation Created', 'title' => $global->name . ' Escalation Created <b>By' . ' ' . $newData->staff->lastName . ' ' . $newData->staff->firstName . '</b>', 'type' => 10,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                PatientTimeLine::create($timeLine);

                $audit = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'escalationId' => $lastId, 'entityType' => 'Created', 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'locationEntityType' => $entityType];
                EscalationAudit::create($audit);

                $result = Escalation::with("type")->where("escalationId", $lastId)->first();

                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'escalations', 'tableId' => $result->escalationId, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $userdata = fractal()->item($result)->transformWith(new EscalationTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $userdata);
                return $endData;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Escalation Assign to Staff
    public function escalationAssignAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $escalation = Escalation::where('udid', $id)->first();
            $detail = $request->input('assign');
            $data = ['isActive' => 0, 'isDelete' => 1, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            EscalationAssignTo::where('escalationId', $escalation->escalationId)->update($data);
            EscalationAssignTo::where('escalationId', $escalation->escalationId)->delete();
            foreach ($detail as $value) {
                if (!empty($value['referenceId'])) {
                    foreach ($value['referenceId'] as $input) {
                        $staff = Staff::where("udid", $input)->first();
                        if (!empty($staff)) {
                            $staffName = $staff->lastName;
                            $staffPhone = $staff->phoneNumber;
                            $namePass = substr($staffName, -3);
                            $phonePass = substr($staffPhone, -3);
                            $escalationPassword = $namePass . $phonePass;
                            $reference = Helper::tableName('App\Models\Staff\Staff', $input);
                            $input = [
                                'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'createdBy' => Auth::id(), 'locationEntityType' => $entityType,
                                'escalationId' => $escalation->escalationId, 'entityType' => $value['entityType'], 'referenceId' => $reference, 'escalationPassword' => $escalationPassword
                            ];
                        } else {
                            $reference = Helper::tableName('App\Models\Referral\Referral', $input);
                            $refralData = Referral::where('id', $reference)->first();
                            $reffName = $refralData->lastName;
                            $reffPhone = $refralData->phoneNumber;
                            $namePass = substr($reffName, -3);
                            $phonePass = substr($reffPhone, -3);
                            $escalationPassword = $namePass . $phonePass;
                            $input = [
                                'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'createdBy' => Auth::id(), 'locationEntityType' => $entityType,
                                'escalationId' => $escalation->escalationId, 'entityType' => 'referral', 'referenceId' => $reference, 'escalationPassword' => $escalationPassword
                            ];
                        }
                        EscalationAssignTo::create($input);
                    }
                }
            }
            $result = EscalationAssignTo::where('escalationId', $escalation->escalationId)->get();
            $userdata = fractal()->collection($result)->transformWith(new EscalationAssignToTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Escalation Assign to staff Listing
    public function escalationAssignList($request, $id, $assignId)
    {
        try {
            $data = EscalationAssignTo::select('escalationAssignTo.*')->with('staff')
                ->leftJoin('staffs', 'staffs.id', '=', 'escalationAssignTo.referenceId');

            // $data->leftJoin('providers', 'providers.id', '=', 'escalationAssignTo.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'escalationAssignTo.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('escalationAssignTo.providerLocationId', '=', 'providerLocations.id')->where('escalationAssignTo.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('escalationAssignTo.providerLocationId', '=', 'providerLocationStates.id')->where('escalationAssignTo.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('escalationAssignTo.providerLocationId', '=', 'providerLocationCities.id')->where('escalationAssignTo.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('escalationAssignTo.providerLocationId', '=', 'subLocations.id')->where('escalationAssignTo.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('escalationAssignTo.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['escalationAssignTo.providerLocationId', $providerLocation], ['escalationAssignTo.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['escalationAssignTo.providerLocationId', $providerLocation], ['escalationAssignTo.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['escalationAssignTo.providerLocationId', $providerLocation], ['escalationAssignTo.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['escalationAssignTo.providerLocationId', $providerLocation], ['escalationAssignTo.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['escalationAssignTo.programId', $program], ['escalationAssignTo.entityType', $entityType]]);
            // }
            if (!$assignId) {
                if ($request->search) {
                    $data->where(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                }
                if ($request->type) {
                    $data->where('escalationAssignTo.entityType', $request->type);
                }
                $data = $data->select('escalationAssignTo.*')->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new EscalationAssignToTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data->where('escalationAssignTo.udid', $assignId);
                $data = $data->first();
                return fractal()->item($data)->transformWith(new EscalationAssignToTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Escalation Detail
    public function escalationDetailAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $escalation = Escalation::where('udid', $id)->first();
            $data = ['isActive' => 0, 'isDelete' => 1, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            EscalationDetail::where('escalationId', $escalation->escalationId)->update($data);
            EscalationDetail::where('escalationId', $escalation->escalationId)->delete();
            $detail = $request->input('detail');
            foreach ($detail as $value) {
                $fromDate = Helper::date($value['fromDate']);
                $toDate = Helper::date($value['toDate']);
                $input = [
                    'udid' => Str::uuid()->toString(), 'fromDate' => $fromDate, 'toDate' => $toDate, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'createdBy' => Auth::id(),
                    'escalationId' => $escalation->escalationId, 'entityType' => $value['entityType'], 'value' => json_encode($value['value']), 'locationEntityType' => $entityType
                ];
                EscalationDetail::create($input);
            }
            //$this->escalationEmailAdd(null, $id);
            dispatch(new EscaltionMailJob($id));
            $this->addEscalationNotification($id);
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Escalation
    public function updateEscalation($request, $id)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();

            $reference = Helper::tableName('App\Models\Patient\Patient', $request->input('referenceId'));
            $input = ['typeId' => $request->input('type'), 'updatedBy' => Auth::id(), 'entityType' => $request->input('entityType'), 'referenceId' => $reference, 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
            Escalation::where('udid', $id)->update($input);
            $result = Escalation::where("udid", $id)->first();

            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'escalations', 'tableId' => $result->escalationId,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
            ];
            ChangeLog::create($changeLog);
            $result = Escalation::where("udid", $id)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $data = fractal()->item($result)->transformWith(new EscalationTransformer)->toArray();
            $endData = array_merge($message, $data);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Escalation
    public function listEscalation($request, $id)
    {
        try {
            $select = array('escalations.escalationId', 'escalations.udid', 'escalations.referenceId', 'escalations.entityType', 'escalations.reason', 'escalations.createdAt', 'escalations.isActive', 'escalations.typeId', 'escalations.statusId', 'escalations.createdBy', 'escalations.updatedBy');
            $data = Escalation::select($select)->with('assign', 'patient:id,firstName,middleName,lastName,udid,dob,userId,medicalRecordNumber,contactTypeId', 'detail', 'escalationAction', 'escalationClose')
                ->leftJoin('globalCodes', 'globalCodes.id', '=', 'escalations.typeId');
            // $data->leftJoin('providers', 'providers.id', '=', 'escalations.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'escalations.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocations.id')->where('escalations.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationStates.id')->where('escalations.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationCities.id')->where('escalations.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'subLocations.id')->where('escalations.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            if ($request->status) {
                $data->leftJoin('escalationCloses', 'escalationCloses.escalationId', '=', 'escalations.escalationId');
            }
            if ($request->search || $request->orderField) {
                $data->leftJoin('patients', 'patients.id', '=', 'escalations.referenceId')
                    ->leftJoin('globalCodes as g1', 'g1.id', '=', 'escalations.statusId')
                    ->leftJoin('escalationAssignTo', 'escalationAssignTo.escalationId', '=', 'escalations.escalationId')
                    ->leftJoin('staffs', 'staffs.id', '=', 'escalationAssignTo.referenceId')
                    ->leftJoin('escalationDetails', 'escalationDetails.escalationId', '=', 'escalations.escalationId');
            }

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('escalations.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['escalations.programId', $program], ['escalations.entityType', $entityType]]);
            // }
            if (!$id) {
                if ($request->referenceId) {
                    $patient = Helper::tableName('App\Models\Patient\Patient', $request->referenceId);
                    $data->where('escalations.referenceId', $patient);
                }
                if ($request->search) {
                    $data->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere('globalCodes.name', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%");
                }
                if ($request->orderField == 'patientName') {
                    $data->orderBy('patients.firstName', $request->orderBy);
                } elseif ($request->orderField == 'assignedTo') {
                    $data->orderBy('staffs.firstName', $request->orderBy);
                } elseif ($request->orderField == 'escalationType') {
                    $data->orderBy('globalCodes.name', $request->orderBy);
                } elseif ($request->orderField == 'status') {
                    $data->orderBy('g1.name', $request->orderBy);
                } elseif ($request->orderField == 'createdAt') {
                    $data->orderBy('escalations.createdAt', $request->orderBy);
                } else {
                    $data->orderBy('escalations.createdAt', 'DESC');
                }
                if ($request->fromDate == 'undefined' && $request->toDate = 'undefined') {
                    $data;
                } elseif ($request->fromDate && $request->toDate) {
                    $fromDate = Helper::date($request->fromDate);
                    $toDate = Helper::date($request->toDate);
                    $data->where('escalations.createdAt', '>=', $fromDate)->where('escalations.createdAt', '<=', $toDate)->where('escalations.entityType', $request->entityType);
                }
                if ($request->status) {
                    $data->where('escalationCloses.status', $request->status);
                }
                $data = $data->groupBy('escalations.escalationId')->paginate(env('PER_PAGE', 20));
                //print_r(($data));
                // dd($data); exit();
                // return $data;
                return fractal()->collection($data)->transformWith(new EscalationTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data = $data->where('escalations.udid', $id)->first();
                return fractal()->item($data)->transformWith(new EscalationTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Escalation for Notification by Escalation Id
    public function escalationList($request, $id)
    {
        try {
            $select = array('escalations.escalationId', 'escalations.udid', 'escalations.referenceId', 'escalations.entityType', 'escalations.reason', 'escalations.createdAt', 'escalations.isActive', 'escalations.typeId', 'escalations.statusId', 'escalations.createdBy', 'escalations.updatedBy');
            $data = Escalation::select($select)->with('assign', 'patient:id,firstName,middleName,lastName,udid,dob,userId,medicalRecordNumber,contactTypeId', 'detail', 'escalationAction', 'escalationClose')
                ->leftJoin('globalCodes', 'globalCodes.id', '=', 'escalations.typeId');
            // $data->leftJoin('providers', 'providers.id', '=', 'escalations.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'escalations.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocations.id')->where('escalations.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationStates.id')->where('escalations.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationCities.id')->where('escalations.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'subLocations.id')->where('escalations.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            if ($request->status) {
                $data->leftJoin('escalationCloses', 'escalationCloses.escalationId', '=', 'escalations.escalationId');
            }
            if ($request->search || $request->orderField) {
                $data->leftJoin('patients', 'patients.id', '=', 'escalations.referenceId')
                    ->leftJoin('globalCodes as g1', 'g1.id', '=', 'escalations.statusId')
                    ->leftJoin('escalationAssignTo', 'escalationAssignTo.escalationId', '=', 'escalations.escalationId')
                    ->leftJoin('staffs', 'staffs.id', '=', 'escalationAssignTo.referenceId')
                    ->leftJoin('escalationDetails', 'escalationDetails.escalationId', '=', 'escalations.escalationId');
            }

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('escalations.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['escalations.programId', $program], ['escalations.entityType', $entityType]]);
            // }
            if (!$id) {
                if ($request->referenceId) {
                    $patient = Helper::tableName('App\Models\Patient\Patient', $request->referenceId);
                    $data->where('escalations.referenceId', $patient);
                }
                if ($request->search) {
                    $data->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere('globalCodes.name', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%");
                }
                if ($request->orderField == 'patientName') {
                    $data->orderBy('patients.firstName', $request->orderBy);
                } elseif ($request->orderField == 'assignedTo') {
                    $data->orderBy('staffs.firstName', $request->orderBy);
                } elseif ($request->orderField == 'escalationType') {
                    $data->orderBy('globalCodes.name', $request->orderBy);
                } elseif ($request->orderField == 'status') {
                    $data->orderBy('g1.name', $request->orderBy);
                } elseif ($request->orderField == 'createdAt') {
                    $data->orderBy('escalations.createdAt', $request->orderBy);
                } else {
                    $data->orderBy('globalCodes.name', 'DESC');
                }
                if ($request->fromDate && $request->toDate) {
                    $fromDate = Helper::date($request->fromDate);
                    $toDate = Helper::date($request->toDate);
                    $data->where('escalations.createdAt', '>=', $fromDate)->where('escalations.createdAt', '<=', $toDate)->where('escalations.entityType', $request->entityType);
                }
                if ($request->status) {
                    $data->where('escalationCloses.status', $request->status);
                }
                $data = $data->groupBy('escalations.escalationId')->paginate(env('PER_PAGE', 20));

                return fractal()->collection($data)->transformWith(new EscalationTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data = $data->where('escalations.escalationId', $id)->first();
                return fractal()->item($data)->transformWith(new EscalationListTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Listing Escalation Audit
    public function escalationAuditList($request, $id)
    {
        try {
            $data = Escalation::select('escalations.*')->with('assign', 'patient', 'detail', 'escalationAction', 'escalationClose', 'escalationAuditDescription')
                ->leftJoin('patients', 'patients.id', '=', 'escalations.referenceId')
                ->leftJoin('globalCodes', 'globalCodes.id', '=', 'escalations.typeId')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'escalations.statusId')
                ->leftJoin('escalationAssignTo', 'escalationAssignTo.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('staffs', 'staffs.id', '=', 'escalationAssignTo.referenceId')
                ->leftJoin('escalationDetails', 'escalationDetails.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('escalationCloses', 'escalationCloses.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('escalationActions', 'escalationActions.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'escalationActions.actionId');

            // $data->leftJoin('providers', 'providers.id', '=', 'escalations.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'escalations.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocations.id')->where('escalations.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationStates.id')->where('escalations.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationCities.id')->where('escalations.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'subLocations.id')->where('escalations.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('escalations.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['escalations.programId', $program], ['escalations.entityType', $entityType]]);
            // }
            if (!$id) {
                if ($request->referenceId) {
                    $patient = Helper::tableName('App\Models\Patient\Patient', $request->referenceId);
                    $data->where('escalations.referenceId', $patient);
                }
                if ($request->search) {
                    $data->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere('globalCodes.name', 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close');
                }
                if ($request->orderField == 'patientName') {
                    $data->orderBy('patients.firstName', $request->orderBy);
                } elseif ($request->orderField == 'assignedTo') {
                    $data->orderBy('staffs.firstName', $request->orderBy);
                } elseif ($request->orderField == 'escalationType') {
                    $data->orderBy('globalCodes.name', $request->orderBy);
                } elseif ($request->orderField == 'status') {
                    $data->orderBy('g1.name', $request->orderBy);
                } elseif ($request->orderField == 'takenAction') {
                    $data->orderBy('g2.name', $request->orderBy);
                } elseif ($request->orderField == 'createdAt') {
                    $data->orderBy('escalations.createdAt', $request->orderBy);
                } else {
                    $data->orderBy('globalCodes.name', 'DESC');
                }
                if ($request->fromDate && $request->toDate) {
                    $fromDate = Helper::date($request->fromDate);
                    $toDate = Helper::date($request->toDate);
                    $data->where('escalations.createdAt', '>=', $fromDate)->where('escalations.createdAt', '<=', $toDate);
                }
                $data->where('escalationCloses.status', 'Close');
                $data = $data->groupBy('escalations.escalationId')->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new EscalationTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data = $data->where('escalations.udid', $id)->first();
                return fractal()->item($data)->transformWith(new EscalationTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Escalation
    public function deleteEscalation($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $result = Escalation::where("udid", $id)->first();
            if ($result->escalationId) {
                $escalationfData = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                Escalation::where("escalationId", $result->escalationId)->update($escalationfData);
                EscalationAssignTo::where("escalationId", $result->escalationId)->update($escalationfData);
                EscalationAudit::where("escalationId", $result->escalationId)->update($escalationfData);
                EscalationDetail::where("escalationId", $result->escalationId)->update($escalationfData);
                EscalationAction::where("escalationId", $result->escalationId)->update($escalationfData);
                EscalationAuditDescription::where("escalationId", $result->escalationId)->update($escalationfData);
                EscalationClose::where("escalationId", $result->escalationId)->update($escalationfData);
                return response()->json(['message' => trans('messages.deletedSuccesfully')]);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Send Email to Staff of Escalation (Out Network)
    public function escalationEmailAdd($request, $id)
    {
        try {
            $escalation = Escalation::Where('udid', $id)->first();
            $data = EscalationAssignTo::Where('escalationId', $escalation->escalationId)->whereHas('staff', function ($query) {
                $query->whereHas('network', function ($query) {
                    $query->where('name', 'Out');
                });
            })->get();
            if ($data) {

                // email body message
                $msgObj = ConfigMessage::where("type", "escalationAdd")
                    ->where("entityType", "sendMail")
                    ->first();
                // email header
                $msgHeaderObj = ConfigMessage::where("type", "header")
                    ->where("entityType", "sendMail")
                    ->first();
                // email footer
                $msgFooterObj = ConfigMessage::where("type", "footer")
                    ->where("entityType", "sendMail")
                    ->first();
                $i = 0;
              //  print_r($msgHeaderObj); die;

                $patientName = str_replace("  ", " ", ucfirst($escalation->patient->lastName) . ',' . ' ' . ucfirst($escalation->patient->firstName) . ' ' . ucfirst($escalation->patient->middleName));
                //$dob = Helper::dateFormat($escalation->patient->dob);
                //$date = Helper::dateFormat($escalation->createdAt);
                $dob = date('M d, Y', strtotime($escalation->patient->dob));
                $date = date('M d, Y h:i A', strtotime($escalation->createdAt));
                $messageObj = array();
                $pdfArray = array();
                $userIds = array();
                foreach ($data as $assignedTo) {
                    $emailadrs = '';
                    $userId = '';
                    $escalationPassword = $assignedTo->escalationPassword;
                    $fullName = "";
                    $patientRefrrel = PatientReferral::where('patientId', $escalation->patient->id)->first();
                    if (!is_null($patientRefrrel)) {
                        $refrrel = Referral::where('id', $patientRefrrel->referralId)->first();
                        $userEmail[] = $refrrel->email;
                        $emailadrs = $refrrel->email;
                        $fullName = ucfirst($refrrel->lastName) . ' ' . ucfirst($refrrel->firstName);
                        $building = "Block1";

                        $userId = $escalation->patient->id;
                    } else {
                        $staff = Staff::with("user")->where('id', $assignedTo->referenceId)->first();

                        $userEmail[] = $staff->user->email;
                        $emailadrs = $staff->user->email;
                        $fullName = $staff->firstName . " " . $staff->lastName;
                        $building = $staff->building;
                        $userId = $staff->user->id;
                    }
                    $base_url = env('APP_URL');
                    $message = '<tr><td style="padding: 15px;">
                               <p style="font-size: 13px;margin: 0 0 10px;"><span style="width: 150px;display:inline-block">Name</span>: <b>' . $patientName . '</b></p>
                               <p style="font-size: 13px;margin: 0 0 10px;"><span style="width: 150px;display:inline-block">DOB</span> :<b>' . $dob . '</b></p>
                               <p style="font-size: 13px;margin: 0 0 10px;"><span style="width: 150px;display:inline-block">Physcian Name</span> : <b>' . $fullName . '</b></p>
                               <p style="font-size: 13px;margin: 0 0 10px;"><span style="width: 150px;display:inline-block">Practice(Building)</span> : <b>' . $building . '</b></p>
                               <p style="font-size: 13px;margin: 0 0 10px;"><span style="width: 150px;display:inline-block">Escalation Date</span>: <b>' . $date . '</b></p>
                               <p style="font-size: 13px;margin: 0 0 10px;"><span style="width: 150px;display:inline-block">Escalation Password</span>: <b>' . $escalationPassword . '</b></p>
                               <td style="padding: 15px;" vertical-align="top">
                               <div>
                               <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $base_url . urlencode("#") . '/escalation-action/' . $id . '&choe=UTF-8" title="Link to Escalation" style="width:150px ;display: block; height:150px"/>
                               </div></td></tr>';

                    $qrCode = time() . '.png';
                    $pdfData = array();
                    $pdfData['name'] = $patientName;
                    $pdfData['dob'] = $dob;
                    $pdfData['physcian'] = $fullName;
                    $pdfData['building'] = $building;
                    $pdfData['date'] = $date;
                    $pdfData['escalationPassword'] = $escalationPassword;
                    $urlQr = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . $base_url . rawurlencode("#") . "/escalation-action/" . $id . "&choe=UTF-8";
                    $image = Helper::getQrCode($urlQr);
                    Storage::disk('public')->put($qrCode, $image);
                    $file = base64_encode(file_get_contents(Storage::disk('public')->path($qrCode)));
                    Storage::disk('public')->delete($qrCode);
                    $pdfData['ids'] = $file;
                    $pdfService = new DomPdfService();
                    $filename = $pdfService->createPdfFile($pdfData, 'escaltion_pdf_file');
                    $pdfArray[$emailadrs] = $filename;
                    $userIds[$emailadrs] = $userId;
                    // echo $filename; die('herere');
                    $variablesArr = array(
                        "fullName" => $fullName,
                        "message" => $message,
                        "heading" => 'Escalation',
                    );
                    if (isset($msgObj->messageBody)) {
                        $messageBody = $msgObj->messageBody;
                        if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                            $messageBody = $msgHeaderObj->messageBody . $messageBody;
                        }
                        if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                            $messageBody = $messageBody . $msgFooterObj->messageBody;
                        }
                        $messageObj[] = ''; //Helper::getMessageBody($messageBody, $variablesArr);
                    }
                    $i++;
                }
                //  print_r($userEmail); die('STOP');
                if (isset($userEmail) && !empty($userEmail)) {
                    $to = $userEmail;
                    if (isset($msgObj->otherParameter)) {
                        $otherParameter = json_decode($msgObj->otherParameter);
                        if (isset($otherParameter->fromName)) {
                            $fromName = $otherParameter->fromName;
                        } else {
                            $fromName = "Virtare Health";
                        }
                    } else {
                        $fromName = "Virtare Health";
                    }
                    if (isset($msgObj->subject)) {
                        $subject = $msgObj->subject;
                    } else {
                        $subject = "New Escalation Created";
                    }

                    Helper::sendInBulkMail($to, $fromName, $messageObj, $subject, $pdfArray, $userIds, 'Escalation', $escalation->escalationId);
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Send Notification to Staff of Escalation (In Network)
    public function addEscalationNotification($id, $title = '', $body = '')
    {
        try {
            $escalation = Escalation::Where('udid', $id)->first();
            $data = EscalationAssignTo::Where('escalationId', $escalation->escalationId)->whereHas('staff', function ($query) {
                $query->whereHas('network', function ($query) {
                    $query->where('name', 'In');
                });
            })->get();
            if ($data->count()>0) {
                $provider = Helper::providerId();
                $providerLocation = Helper::providerLocationId();
                foreach ($data as $assignedTo) {
                    $staff = Staff::with("user")->where('id', $assignedTo->referenceId)->first();
                    $UserId = $staff->userId;
                    if (!empty($title) && !empty($body)) {
                        $title = $title;
                        $body = $body;
                    } else {
                        $title = 'Escalation Created';
                        $body = 'New Escalation Created.';
                    }
                    $notificationData = [
                        'body' => $body,
                        'title' => $title,
                        'userId' => $UserId,
                        'isSent' => 0,
                        'entity' => 'Escalation',
                        'referenceId' => $escalation->escalationId,
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation,
                    ];
                    $notification = Notification::create($notificationData);
                    
                    event(new NotificationEvent($notification));
                    $notificationUpdate = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
                    Notification::where('id', $notification->id)->update($notificationUpdate);

                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $provider,
                        'providerLocationId' => $providerLocation,
                        'value' => json_encode($notificationData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                    
                    return fractal()->item($notification)->transformWith(new NotificationTransformer())->toArray();
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Escalation Action
    public function escalationActionAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $escalation = Escalation::where('udid', $id)->first();
            $input = [
                'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'escalationId' => $escalation->escalationId, 'actionId' => $request->action,
                'note' => $request->note, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
            ];
            $action = EscalationAction::create($input);

            $audit = [
                'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'escalationId' => $escalation->escalationId, 'entityType' => 'Responded', 'providerId' => $provider,
                'providerLocationId' => $providerLocation, 'locationEntityType' => $entityType,
            ];
            EscalationAudit::create($audit);

            $userInput = Staff::where('udid', $request->physician)->first();
            $timeLine = [
                'patientId' => $escalation->referenceId, 'heading' => 'Escalation Response', 'title' => $request->input('note') . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 10,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType,
            ];
            PatientTimeLine::create($timeLine);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'escalationActions', 'tableId' => $action->id, 'entityType' => $entityType,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            ChangeLog::create($changeLog);

            $inputEscalation = ['updatedBy' => Auth::id(), 'statusId' => 352];
            Escalation::where('udid', $id)->update($inputEscalation);
            $result = EscalationAction::where('id', $action->id)->first();
            $userdata = fractal()->item($result)->transformWith(new EscalationActionTransformer())->toArray();
            $this->addEscalationNotification($id);
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Escalation Action Listing
    public function escalationActionList($request, $id, $actionId)
    {
        try {
            $data = EscalationAction::select('escalationAudits.*')->with('action');

            // $data->leftJoin('providers', 'providers.id', '=', 'escalationAudits.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'escalationAudits.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'providerLocations.id')->where('escalationAudits.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'providerLocationStates.id')->where('escalationAudits.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'providerLocationCities.id')->where('escalationAudits.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'subLocations.id')->where('escalationAudits.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('escalationAudits.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['escalationAudits.programId', $program], ['escalationAudits.entityType', $entityType]]);
            // }
            if (!$actionId) {
                $escalation = Escalation::where('udid', $id)->first();
                $data = $data->where('escalationActions.escalationId', $escalation->escalationId)->get();
                return fractal()->collection($data)->transformWith(new EscalationActionTransformer())->toArray();
            } else {
                $data = $data->where('escalationActions.udid', $actionId)->first();
                return fractal()->item($data)->transformWith(new EscalationActionTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Escalation Resend
    public function escalationResend($request, $id)
    {
        try {
            $escalation = Escalation::where('udid', $id)->first();
            if ($escalation->type->name == 'Urgent') {
                $time = Carbon::now()->addHour(2);
            } else {
                $time = Carbon::now()->addHour(6);
            }
            $escalationAction = EscalationAction::where('escalationId', $escalation->escalationId)->first();
            if (is_null($escalationAction)) {
                $data = EscalationAssignTo::where('escalationId', $escalation->escalationId)->where('createdAt', '<=', $time)->whereHas('staff', function ($query) {
                    $query->whereHas('network', function ($query) {
                        $query->where('name', 'Out');
                    });
                })->get();

                if ($data) {

                    // email body message
                    $msgObj = ConfigMessage::where("type", "escalationAdd")
                        ->where("entityType", "sendMail")
                        ->first();
                    // email header
                    $msgHeaderObj = ConfigMessage::where("type", "header")
                        ->where("entityType", "sendMail")
                        ->first();
                    // email footer
                    $msgFooterObj = ConfigMessage::where("type", "footer")
                        ->where("entityType", "sendMail")
                        ->first();
                    $i = 0;
                    $messageObj = array();
                    $userIds = array();
                    foreach ($data as $assignedTo) {
                        $fullName = "";
                        $staff = Staff::with("user")->where('id', $assignedTo->referenceId)->first();
                        $userEmail[] = $staff->user->email;
                        $userIds[$staff->user->email] = $staff->user->id;
                        $fullName = $staff->firstName . " " . $staff->lastName;
                        $message = 'Hey! You Missed An Escalation to Respond';
                        $variablesArr = array(
                            "fullName" => $fullName,
                            "message" => $message,
                            "heading" => 'Escalation',
                        );
                        if (isset($msgObj->messageBody)) {
                            $messageBody = $msgObj->messageBody;
                            if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                                $messageBody = $msgHeaderObj->messageBody . $messageBody;
                            }
                            if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                                $messageBody = $messageBody . $msgFooterObj->messageBody;
                            }
                            $messageObj[] = Helper::getMessageBody($messageBody, $variablesArr);
                        }
                        $i++;
                    }
                    if (isset($userEmail) && !empty($userEmail)) {
                        $to = $userEmail;
                        if (isset($msgObj->otherParameter)) {
                            $otherParameter = json_decode($msgObj->otherParameter);
                            if (isset($otherParameter->fromName)) {
                                $fromName = $otherParameter->fromName;
                            } else {
                                $fromName = "Virtare Health";
                            }
                        } else {
                            $fromName = "Virtare Health";
                        }
                        if (isset($msgObj->subject)) {
                            $subject = $msgObj->subject;
                        } else {
                            $subject = "New Escalation Created";
                        }
                        Helper::sendInBulkMail($to, $fromName, $messageObj, $subject, array(), $userIds, 'Escalation', $escalation->escalationId);
                    }
                }
                $title = 'Escalation';
                $body = 'Hey! You Missed An Escalation to Respond';
                $this->addEscalationNotification($id, $title, $body);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Escalation Audit Listing
    public function escalationAudit($request, $id)
    {
        try {
            $escalation = Escalation::where('udid', $id)->first();
            $data = EscalationAudit::select('escalationAudits.*')->where('escalationId', $escalation->escalationId);

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'providerLocations.id')->where('escalationAudits.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'providerLocationStates.id')->where('escalationAudits.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'providerLocationCities.id')->where('escalationAudits.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('escalationAudits.providerLocationId', '=', 'subLocations.id')->where('escalationAudits.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('escalationAudits.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['escalationAudits.providerLocationId', $providerLocation], ['escalationAudits.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['escalationAudits.programId', $program], ['escalationAudits.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new EscalationAuditTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Verify Email to out side staff
    public function verifyEscalation($request, $id)
    {
        try {
            $escalation = Escalation::where('udid', $id)->first();
            $escalationPassword = $request->input('escalationPassword');
            $escalationAssigne = EscalationAssignTo::where([['escalationId', $escalation->escalationId], ['escalationPassword', $escalationPassword]])->first();
            if (!is_null($escalationAssigne)) {
                $staffId = $escalationAssigne->referenceId;
                $userData = Staff::where('id', $staffId)->first();
                $userId = $userData->userId;
                $user = User::where('id', $userId)->first();
                $token = auth()->login($user);
                $data = array(
                    'token' => $token,
                );
                return $data;
            } else {
                return response()->json(['message' => 'Invalid Credential'], 422);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Escalation close by staff
    public function addEscalationActionClose($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $escalation = Escalation::where('udid', $id)->first();
            $input = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'escalationId' => $escalation->escalationId, 'status' => 'Close', 'description' => $request->input('description'), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            $escalationClose = EscalationClose::create($input);
            $audit = [
                'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'escalationId' => $escalation->escalationId, 'entityType' => 'Escalation Close', 'providerId' => $provider,
                'providerLocationId' => $providerLocation, 'locationEntityType' => $entityType,
            ];
            EscalationAudit::create($audit);
            $timeLine = [
                'patientId' => $escalation->referenceId, 'heading' => 'Escalation Response', 'title' => $request->input('description') . ' ' . '<b>By' . ' ' . auth()->user()->staff->lastName . ',' . ' ' . auth()->user()->staff->firstName . '</b>', 'type' => 10,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType,
            ];
            PatientTimeLine::create($timeLine);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'escalationClose', 'tableId' => $escalationClose->id, 'entityType' => $entityType,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            ChangeLog::create($changeLog);
            return response()->json(["message" => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Escalation Audit Description
    public function addEscalationAuditDescription($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $escalation = Escalation::where('udid', $id)->first();
            $input = [
                'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'escalationId' => $escalation->escalationId, 'description' => $request->input('description'),
                'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
            ];
            $escalationAuditDesc = EscalationAuditDescription::create($input);
            $audit = [
                'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'escalationId' => $escalation->escalationId, 'entityType' => 'Escalation Close Description Added', 'providerId' => $provider,
                'providerLocationId' => $providerLocation, 'locationEntityType' => $entityType,
            ];
            EscalationAudit::create($audit);
            $timeLine = [
                'patientId' => $escalation->referenceId, 'heading' => 'Escalation Response', 'title' => $request->input('description') . ' ' . '<b>By' . ' ' . auth()->user()->staff->lastName . ',' . ' ' . auth()->user()->staff->firstName . '</b>', 'type' => 10,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType,
            ];
            PatientTimeLine::create($timeLine);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'escalationAuditDescriptions', 'tableId' => $escalationAuditDesc->id, 'entityType' => $entityType,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            ChangeLog::create($changeLog);
            return response()->json(["message" => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
