<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Flag\Flag;
use App\Models\Note\Note;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient\PatientTimeLog;
use App\Models\CPTCode\CptCodeActivity;
use App\Models\Patient\PatientTimeLine;
use App\Models\AuditLogs\ChangeAuditLog;
use App\Transformers\Patient\PatientTimeLogTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\AuditTimeLog\AuditTimeLogTransformer;

class TimeLogService
{
    // List Timelog
    public function timeLogList($request, $id)
    {
        try {
            $data = PatientTimeLog::select('patientTimeLogs.*')->with('logged', 'performed', 'patient')
                ->leftJoin('staffs as s1', 's1.id', '=', 'patientTimeLogs.performedId')
                ->leftJoin('staffs as s2', 's2.id', '=', 'patientTimeLogs.loggedId')
                ->leftJoin('cptCodeActivities', 'cptCodeActivities.id', '=', 'patientTimeLogs.cptCodeId')
                ->leftJoin('cptCodes', 'cptCodes.id', '=', 'cptCodeActivities.cptCodeId')
                ->leftJoin('patients', 'patients.id', '=', 'patientTimeLogs.patientId')
                ->where(function ($query) {
                    $query->where('cptCodes.id', '!=', 6)
                        ->orWhere('patientTimeLogs.cptCodeId', 0)
                        ->orWhereNull('patientTimeLogs.cptCodeId');
                });

            // $data->leftJoin('providers', 'providers.id', '=', 'patientTimeLogs.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientTimeLogs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocations.id')->where('patientTimeLogs.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocationStates.id')->where('patientTimeLogs.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocationCities.id')->where('patientTimeLogs.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'subLocations.id')->where('patientTimeLogs.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');


            if ($request->search) {
                $value = explode(',', $request->search);
                $data->where(function ($query) use ($value) {
                    foreach ($value as $key => $search) {
                        if ($key == '0') {
                            $query->where(DB::raw("CONCAT(trim(`s1`.`firstName`), ' ', trim(`s1`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`s2`.`firstName`), ' ', trim(`s2`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $search . "%");
                        } else {
                            $query->orwhere(DB::raw("CONCAT(trim(`s1`.`firstName`), ' ', trim(`s1`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`s2`.`firstName`), ' ', trim(`s2`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $search . "%");
                        }
                    }
                });
            }
            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientTimeLogs.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientTimeLogs.programId', $program], ['patientTimeLogs.entityType', $entityType]]);
            // }
            if ($request->filter) {
                $data->where('cptCodes.name', $request->filter);
            }
            if (!empty($request->fromDate) && !empty($request->toDate)) {
                // $data->whereBetween('date', [$request->fromDate, $request->toDate]);
                $data->where([['date', '>=', $request->fromDate], ['date', '<=', $request->toDate]]);
            }
            if ($request->all) {
                if ($request->toDate && $request->fromDate) {
                    $data = $data->where([['date', '>=', $request->fromDate], ['date', '<=', $request->toDate]])->get();
                } else {
                    $data = $data->with('category', 'logged', 'performed', 'notes')->get();
                }
                return fractal()->collection($data)->transformWith(new PatientTimeLogTransformer())->toArray();
            }
            if ($id) {
                $data = $data->where('patientTimeLogs.udid', $id)->first();
                return fractal()->item($data)->transformWith(new PatientTimeLogTransformer())->toArray();
            }
            if ($request->orderField == 'performedBy') {
                $data->orderBy('s1.firstName', $request->orderBy);
            } elseif ($request->orderField == 'patient') {
                $data->orderBy('patients.firstName', $request->orderBy);
            } elseif ($request->orderField == 'timeAmount' || $request->orderField == 'date') {
                $data->orderBy($request->orderField, $request->orderBy);
            } else {
                $data->orderBy('patientTimeLogs.createdAt', "DESC");
            }
            $data->groupBy('patientTimeLogs.id');
            $data = $data->paginate(env('PER_PAGE', 20));
            return fractal()->collection($data)->transformWith(new PatientTimeLogTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Timelog
    public function timeLogUpdate($request, $id)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            if ($request->input('flag')) {
                $flag = Flag::where('udid', $request->input('flag'))->first();
            }
            if ($request->cptCode) {
                $cpt = CptCodeActivity::where('udid', $request->cptCode)->first();
                if ($cpt) {
                    $cptId = $cpt->id;
                } else {
                    $cptId = NULL;
                }
            } else {
                $cptId = NULL;
            }
            if ($request->input('noteId')) {
                $noteData = ['note' => $request->input('note'), 'updatedBy' => Auth::id(), 'flagId' => $flag->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                Note::where('id', $request->input('noteId'))->update($noteData);
                $note = Note::where('id', $request->input('noteId'))->first();
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $request->input('noteId'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($noteData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            } elseif ($request->input('note')) {
                $timeLog = PatientTimeLog::where('udid', $id)->first();
                $patientId = Patient::where('id', $timeLog->patientId)->first();
                $noteData = [
                    'note' => $request->input('note'), 'entityType' => 'auditlog', 'referenceId' => $timeLog->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'categoryId' => 155, 'type' => 153, 'flagId' => $flag->id
                ];
                $note = Note::create($noteData);
                if (auth()->user()->roleId == 4) {
                    $userInput = Patient::where('id', auth()->user()->patient->id)->first();
                } else {
                    $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                }
                $timeLine = [
                    'patientId' => $patientId->id, 'heading' => 'Note Added', 'title' => $request->input('note') . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 6,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                PatientTimeLine::create($timeLine);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $note->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($noteData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $input = [
                'timeAmount' => $request->input('timeAmount'), 'updatedBy' => Auth::id(), 'cptCodeId' => $cptId,
                'categoryId' => $request->input('category'), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            PatientTimeLog::where('udid', $id)->update($input);
            $petientTime = PatientTimeLog::where('udid', $id)->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientTimeLogs', 'tableId' => $petientTime->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $patientTimelogId = PatientTimeLog::where('udid', $id)->first();
            $timeLog = [
                'udid' => Str::uuid()->toString(), 'timeAmount' => $request->input('timeAmount'), 'note' => $request->input('note'),
                'createdBy' => Auth::id(), 'patientTimeLogId' => $patientTimelogId->id, 'flagId' => $flag->id, 'cptCodeId' => $cptId, 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            $audit = ChangeAuditLog::create($timeLog);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'changeAuditLogs', 'tableId' => $audit->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($timeLog), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $data = PatientTimeLog::where('udid', $id)->with('category', 'logged', 'performed', 'patient.notes')->first();
            $userdata = fractal()->item($data)->transformWith(new PatientTimeLogTransformer())->toArray();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            DB::commit();
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }

    // Delete Timelog
    public function timeLogDelete($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientTimeLog::where('udid', $id)->update($input);
            $data = Helper::entity('timeLog', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientTimeLogs', 'tableId' => $data, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            PatientTimeLog::where('udid', $id)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add and Update Patient TimeLog
    public function patientTimeLogAdd($request, $entityType, $id, $timelogId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $patientId = Patient::where('udid', $id)->first();
            if (!$timelogId) {
                $dateConvert = Helper::date($request->input('date'));
                $performedBy = Helper::entity('staff', $request->input('performedBy'));
                $loggedBy = Helper::entity('staff', $request->input('loggedBy'));
                $cpt = CptCodeActivity::where('udid', $request->cptCode)->first();
                if ($request->input('category')) {
                    $category = $request->input('category');
                } else {
                    $category = '';
                }
                if ($cpt) {
                    $cptId = $cpt->id;
                } else {
                    $cptId = 13;
                }
                $input = [
                    'categoryId' => $category, 'loggedId' => $loggedBy, 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'performedId' => $performedBy, 'date' => $dateConvert, 'timeAmount' => $request->input('timeAmount'),
                    'createdBy' => Auth::id(), 'patientId' => $patientId->id, 'cptCodeId' => $cptId
                ];
                $data = PatientTimeLog::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientTimeLogs', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $flag = Flag::where('udid', $request->input('flag'))->first();
                $note = [
                    'note' => $request->input('note'), 'entityType' => 'auditlog', 'referenceId' => $data->id, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(),
                    'flagId' => $flag->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $noteData = Note::create($note);
                $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                $timeLine = [
                    'patientId' => $patientId->id, 'heading' => 'TimeLog Note Added', 'title' => $request->input('note') . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 6,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                PatientTimeLine::create($timeLine);

                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $noteData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($note), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);


                $timeLog = [
                    'udid' => Str::uuid()->toString(), 'timeAmount' => $request->input('timeAmount'), 'note' => $request->input('note'),
                    'createdBy' => Auth::id(), 'patientTimeLogId' => $data->id, 'flagId' => @$flag->id, 'cptCodeId' => $cptId, 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $audit = ChangeAuditLog::create($timeLog);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'changeAuditLogs', 'tableId' => $audit->id,
                    'value' => json_encode($timeLog), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);

                $timeLog = PatientTimeLog::where('id', $data->id)->first();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $result = fractal()->item($timeLog)->transformWith(new PatientTimeLogTransformer())->toArray();
                $data = array_merge($message, $result);
            } else {
                $dateConvert = Helper::date($request->input('date'));
                $cpt = CptCodeActivity::where('udid', $request->cptCode)->first();
                $timeLog = array();
                if (!empty($request->category)) {
                    $timeLog['categoryId'] = $request->category;
                }
                if (!empty($request->loggedBy)) {
                    $loggedBy = Helper::entity('staff', $request->input('loggedBy'));
                    $timeLog['loggedId'] = $loggedBy;
                }
                if (!empty($request->performedBy)) {
                    $performedBy = Helper::entity('staff', $request->input('performedBy'));
                    $timeLog['performedId'] = $performedBy;
                }
                if (!empty($request->date)) {
                    $timeLog['date'] = $dateConvert;
                }
                if (!empty($request->timeAmount)) {
                    $timeLog['timeAmount'] = $request->input('timeAmount');
                }
                if (!empty($request->cptCode)) {
                    $timeLog['cptCodeId'] = $cpt->id;
                }
                $timeLog['updatedBy'] = Auth::id();
                $timeLog['providerId'] = $provider;
                $timeLog['providerLocationId'] = $providerLocation;
                $data = PatientTimeLog::where('udid', $timelogId)->update($timeLog);
                $timeLogData = Helper::entity('timeLog', $timelogId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientTimeLogs', 'tableId' => $timeLogData, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($timeLog), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                if ($request->input('noteId')) {
                    $noteData = ['note' => $request->input('note'), 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                    Note::where('id', $request->input('noteId'))->update($noteData);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $request->input('noteId'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($noteData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);

                    $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                    $timeLine = [
                        'patientId' => $patientId->id, 'heading' => 'TimeLog Note Added', 'title' => $request->input('note') . ' ' . '<b>By' . ' ' . $userInput->lastName . ','  . ' ' . $userInput->firstName . '</b>', 'type' => 6,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider
                    ];
                    PatientTimeLine::create($timeLine);
                    
                } elseif ($request->input('note')) {
                    $time = PatientTimeLog::where('udid', $timelogId)->first();
                    $flag = Flag::where('udid', $request->input('flag'))->first();
                    $noteData = [
                        'note' => $request->input('note'), 'entityType' => $request->input('entityType'), 'referenceId' => $time->id,
                        'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'flagId' => $flag->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation
                    ];
                    $note = Note::create($noteData);
                    $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                    $timeLine = [
                        'patientId' => $patientId->id, 'heading' => 'TimeLog Note Added', 'title' => $request->input('note') . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 6,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                    ];
                    PatientTimeLine::create($timeLine);

                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $note->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($noteData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
                $patientTimeLogId = PatientTimeLog::where('udid', $timelogId)->first();
                $timeLog = [
                    'udid' => Str::uuid()->toString(), 'timeAmount' => $request->input('timeAmount'), 'note' => $request->input('note'),
                    'createdBy' => Auth::id(), 'patientTimeLogId' => $patientTimeLogId->id, 'flagId' => @$flag->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $auditData = ChangeAuditLog::create($timeLog);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'changeAuditLogs', 'tableId' => $auditData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($timeLog), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $timeLog = PatientTimeLog::where('udid', $timelogId)->first();
                $message = ['message' => trans('messages.updatedSuccesfully')];
                $result = fractal()->item($timeLog)->transformWith(new PatientTimeLogTransformer())->toArray();
            }
            DB::commit();
            $data = array_merge($message, $result);
            return $data;
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }

    // List Patient TimeLog
    public function patientTimeLogList($request, $entity, $id, $timelogId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = PatientTimeLog::select('patientTimeLogs.*')->with('category', 'logged', 'performed', 'notes');


            // $data->leftJoin('providers', 'providers.id', '=', 'patientTimeLogs.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientTimeLogs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocations.id')->where('patientTimeLogs.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocationStates.id')->where('patientTimeLogs.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocationCities.id')->where('patientTimeLogs.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'subLocations.id')->where('patientTimeLogs.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientTimeLogs.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientTimeLogs.programId', $program], ['patientTimeLogs.entityType', $entityType]]);
            // }
            if (!$timelogId) {
                $patient = Helper::entity($entity, $id);
                $data = $data->where('patientTimeLogs.patientId', $patient)->latest()->get();
                return fractal()->collection($data)->transformWith(new PatientTimeLogTransformer())->toArray();
            } else {
                $data = $data->where('patientTimeLogs.udid', $timelogId)->first();
                return fractal()->item($data)->transformWith(new PatientTimeLogTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient TimeLog
    public function patientTimeLogDelete($request, $entity, $id, $timelogId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientTimeLog::where('udid', $timelogId)->update($data);
            $input = Helper::entity('timeLog', $timelogId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientTimeLogs', 'tableId' => $input, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            PatientTimeLog::where('udid', $timelogId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }

    // List Change Audit Time Log
    public function auditLogChange($request, $id)
    {
        try {
            $data = ChangeAuditLog::select('changeAuditLogs.*')->with('user', 'flag', 'cptCode');

            // $data->leftJoin('providers', 'providers.id', '=', 'changeAuditLogs.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'changeAuditLogs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');


            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('changeAuditLogs.providerLocationId', '=', 'providerLocations.id')->where('changeAuditLogs.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('changeAuditLogs.providerLocationId', '=', 'providerLocationStates.id')->where('changeAuditLogs.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('changeAuditLogs.providerLocationId', '=', 'providerLocationCities.id')->where('changeAuditLogs.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('changeAuditLogs.providerLocationId', '=', 'subLocations.id')->where('changeAuditLogs.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('changeAuditLogs.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['changeAuditLogs.providerLocationId', $providerLocation], ['changeAuditLogs.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['changeAuditLogs.providerLocationId', $providerLocation], ['changeAuditLogs.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['changeAuditLogs.providerLocationId', $providerLocation], ['changeAuditLogs.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['changeAuditLogs.providerLocationId', $providerLocation], ['changeAuditLogs.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['changeAuditLogs.programId', $program], ['changeAuditLogs.entityType', $entityType]]);
            // }
            $patientTimeLogId = PatientTimeLog::where('udid', $id)->first();
            $data = $data->where('changeAuditLogs.patientTimeLogId', $patientTimeLogId->id)->get();
            return fractal()->collection($data)->transformWith(new AuditTimeLogTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
