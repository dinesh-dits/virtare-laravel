<?php

namespace App\Services\Api;

use App\Helper;
use Carbon\Carbon;
use App\Models\Note\Note;
use Exception;
use Illuminate\Support\Str;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient\PatientTimeLog;
use App\Models\CPTCode\CptCodeActivity;
use App\Models\TimeApproval\TimeApproval;
use App\Models\Patient\PatientTimelogReference;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\TimeApproval\TimeApprovalTransformer;

class TimeApprovalService
{
    // Add Time Approval
    public function addTimeApproval($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = Helper::tableName('App\Models\Staff\Staff', $request->staff);
            $patientId = Helper::tableName('App\Models\Patient\Patient', $request->patient);
            if ($request->status) {
                $status = $request->status;
            } else {
                $status = 328;
            }
            $referenceIdx = Patient::where('udid', $request->input('referenceId'))->first();
            if ($referenceIdx) {
                $referenceId = $referenceIdx->id;
            } else {
                $referenceId = $request->input('referenceId');
            }
            $input = [
                'staffId' => $staffId, 'udid' => Str::uuid()->toString(), 'patientId' => $patientId, 'time' => $request->input('time'), 'typeId' => $request->type, 'statusId' => $status,
                'entityType' => $request->input('entityType'), 'referenceId' => $referenceId, 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            $timeId = TimeApproval::create($input);
            $time = TimeApproval::where('id', $timeId->id)->first();
            $userdata = fractal()->item($time)->transformWith(new TimeApprovalTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Time Approval
    public function listTimeApproval($request, $id)
    {
        try {
            if (!$id) {
                $data = TimeApproval::select('timeApprovals.*')->where([['timeApprovals.createdBy', Auth::id()], ['statusId', '!=', 329]])
                    ->leftJoin('patients', 'patients.id', '=', 'timeApprovals.patientId')
                    ->leftJoin('globalCodes as g2', 'g2.id', '=', 'timeApprovals.typeId')
                    ->leftJoin('globalCodes as g1', 'g1.id', '=', 'timeApprovals.statusId');
                if ($request->search) {
                    $data->where(function ($query) use ($request) {
                        $query->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                            ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                            ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                            ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                            ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%")
                            ->orWhere('g2.name', 'LIKE', "%" . $request->search . "%");
                    });
                }

                if (!empty($request->fromDate) && !empty($request->toDate)) {
                    $fromDate = $request->fromDate . " 00:00:00";
                    $toDate = $request->toDate . " 23:59:59";
                    $data->whereBetween('timeApprovals.createdAt', [$fromDate, $toDate]);
                }

                if ($request->orderField == 'patient') {
                    $data->orderBy('patients.firstName', $request->orderBy);
                } elseif ($request->orderField == 'time') {
                    $data->orderBy($request->orderField, $request->orderBy);
                } elseif ($request->orderField == 'status') {
                    $data->orderBy('g1.name', $request->orderBy);
                } elseif ($request->orderField == 'type') {
                    $data->orderBy('g2.name', $request->orderBy);
                } elseif ($request->orderField == 'createdAt') {
                    $data->orderBy('timeApprovals.createdAt', $request->orderBy);
                } else {
                    $data->orderBy('timeApprovals.createdAt', 'DESC');
                }
                $data = $data->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new TimeApprovalTransformer(false))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data = TimeApproval::select('timeApprovals.*');
                // $data->leftJoin('providers', 'providers.id', '=', 'timeApprovals.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
                // $data->leftJoin('programs', 'programs.id', '=', 'timeApprovals.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

                // $data->leftJoin('providerLocations', function ($join) {
                //     $join->on('timeApprovals.providerLocationId', '=', 'providerLocations.id')->where('timeApprovals.entityType', '=', 'Country');
                // })->whereNull('providerLocations.deletedAt');

                // $data->leftJoin('providerLocationStates', function ($join) {
                //     $join->on('timeApprovals.providerLocationId', '=', 'providerLocationStates.id')->where('timeApprovals.entityType', '=', 'State');
                // })->whereNull('providerLocationStates.deletedAt');

                // $data->leftJoin('providerLocationCities', function ($join) {
                //     $join->on('timeApprovals.providerLocationId', '=', 'providerLocationCities.id')->where('timeApprovals.entityType', '=', 'City');
                // })->whereNull('providerLocationCities.deletedAt');

                // $data->leftJoin('subLocations', function ($join) {
                //     $join->on('timeApprovals.providerLocationId', '=', 'subLocations.id')->where('timeApprovals.entityType', '=', 'subLocation');
                // })->whereNull('subLocations.deletedAt');

                // if (request()->header('providerId')) {
                //     $provider = Helper::providerId();
                //     $data->where('timeApprovals.providerId', $provider);
                // }
                // if (request()->header('providerLocationId')) {
                //     $providerLocation = Helper::providerLocationId();
                //     if (request()->header('entityType') == 'Country') {
                //         $data->where([['timeApprovals.providerLocationId', $providerLocation], ['timeApprovals.entityType', 'Country']]);
                //     }
                //     if (request()->header('entityType') == 'State') {
                //         $data->where([['timeApprovals.providerLocationId', $providerLocation], ['timeApprovals.entityType', 'State']]);
                //     }
                //     if (request()->header('entityType') == 'City') {
                //         $data->where([['timeApprovals.providerLocationId', $providerLocation], ['timeApprovals.entityType', 'City']]);
                //     }
                //     if (request()->header('entityType') == 'subLocation') {
                //         $data->where([['timeApprovals.providerLocationId', $providerLocation], ['timeApprovals.entityType', 'subLocation']]);
                //     }
                // }
                // if (request()->header('programId')) {
                //     $program = Helper::programId();
                //     $entityType = Helper::entityType();
                //     $data->where([['timeApprovals.programId', $program], ['timeApprovals.entityType', $entityType]]);
                // }

                $data = $data->where('timeApprovals.udid', $id)->first();
                return fractal()->item($data)->transformWith(new TimeApprovalTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Time in Time Approval
    public function updateTimeApproval($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['time' => $request->time, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            TimeApproval::where('udid', $id)->update($input);
            $time = TimeApproval::where('udid', $id)->first();
            $userdata = fractal()->item($time)->transformWith(new TimeApprovalTransformer())->toArray();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Multiple Time Approval
    public function updateTimeApprovalMultiple($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();

            $timeLogData = array();
            if ($request->status) {
                $timeLogData['statusId'] = $request->status;
            }
            if ($request->type) {
                $timeLogData['typeId'] = $request->type;
            }
            $timeLogData['updatedBy'] = Auth::id();
            $timeLogData['providerId'] = $provider;
            $timeLogData['providerLocationId'] = $providerLocation;
            TimeApproval::whereIn('udid', $request->id)->update($timeLogData);
            $cpt = CptCodeActivity::where('udid', $request->cptCode)->first();
            $dateConvert = Carbon::now();
            $timeData = TimeApproval::where('udid', $request->id[0])->first();
            if ($request->status == 329) {
                $inputData = [
                    'categoryId' => $request->category, 'loggedId' => Auth::id(), 'udid' => Str::uuid()->toString(),
                    'performedId' => $timeData->staffId, 'date' => $dateConvert, 'timeAmount' => $request->input('timeAmount'),
                    'createdBy' => Auth::id(), 'patientId' => $timeData->patientId, 'cptCodeId' => $cpt->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $timelog = PatientTimeLog::create($inputData);
                foreach ($request->id as $timeId) {
                    $timeData = TimeApproval::where('udid', $timeId)->first();
                    $data = [
                        'patientTimeLogId' => $timelog->id, 'timeApprovalId' => $timeData->id, 'createdBy' => Auth::id(),
                        'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                    ];
                    PatientTimelogReference::create($data);
                }
                if ($request->input('note')) {
                    $note = [
                        'note' => $request->input('note'), 'entityType' => 'auditlog', 'referenceId' => $timelog->id, 'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                    ];
                    Note::create($note);
                }
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
