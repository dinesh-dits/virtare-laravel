<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Note\Note;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\PatientGoal;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Patient\PatientGoalTransformer;

class PatientGoalService
{
    // List Patient Goal
    public function index($request, $id, $goalId)
    {
        try {
            $data = PatientGoal::select('patientGoals.*')->with('patient');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientGoals.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientGoals.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientGoals.providerLocationId', '=', 'providerLocations.id')->where('patientGoals.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientGoals.providerLocationId', '=', 'providerLocationStates.id')->where('patientGoals.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientGoals.providerLocationId', '=', 'providerLocationCities.id')->where('patientGoals.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientGoals.providerLocationId', '=', 'subLocations.id')->where('patientGoals.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientGoals.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientGoals.providerLocationId', $providerLocation], ['patientGoals.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientGoals.providerLocationId', $providerLocation], ['patientGoals.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientGoals.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientGoals.providerLocationId', $providerLocation], ['patientGoals.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientGoals.programId', $program], ['patientGoals.entityType', $entityType]]);
            // }
            if ($id) {
                $patient = Helper::entity('patient', $id);
                if ($goalId) {
                    $notAccess = Helper::haveAccess($patient);
                    if ($notAccess) {
                        $data->where([['patientGoals.patientId', $patient], ['patientGoals.udid', $goalId]])->get();
                    } else {
                        return $notAccess;
                    }
                } elseif (!$goalId) {
                    $data->where('patientGoals.patientId', $patient)->with('notes', function ($query) {
                        $query->where('entityType', 'patientGoal');
                    });
                    if (isset($request->fromDate) && isset($request->toDate)) {
                        $fromDateStr = Helper::date($request->fromDate);
                        $toDateStr = Helper::date($request->toDate);
                        $data->whereBetween('patientGoals.createdAt', [$fromDateStr, $toDateStr]);
                    }
                    $data = $data->orderBy('patientGoals.createdAt', 'DESC')->get();
                    // return $data;
                } else {
                    return response()->json(['message' => trans('messages.unauthenticated')], 401);
                }
            } elseif (!$id) {
                if ($goalId) {
                    $data->where([['patientGoals.patientId', auth()->user()->patient->id], ['patientGoals.udid', $goalId]])->get();
                } elseif (!$goalId) {
                    $data->where('patientGoals.patientId', auth()->user()->patient->id);
                    if ($request->currentDate) {
                        $data = $data->where('patientGoals.endDate', '>=', $request->currentDate);
                    }
                    $data = $data->get();
                } else {
                    return response()->json(['message' => trans('messages.unauthenticated')], 401);
                }
            }
            return fractal()->collection($data)->transformWith(new PatientGoalTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Goal
    public function patientGoalAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::entity('patient', $id);
            $startDate = Helper::dateOnly($request->input('startDate'));
            $endDate = Helper::dateOnly($request->input('endDate'));
            $input = [
                'lowValue' => $request->input('lowValue'), 'highValue' => $request->input('highValue'), 'vitalFieldId' => $request->input('vitalField'),
                'startDate' => $startDate, 'endDate' => $endDate, 'frequency' => $request->input('frequency'), 'entityType' => $entityType,
                'frequencyTypeId' => $request->input('frequencyType'), 'deviceTypeId' => $request->input('deviceType'),
                'createdBy' => Auth::id(), 'patientId' => $patient, 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            $goal = PatientGoal::create($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientGoals', 'tableId' => $goal->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);

            $note = [
                'udid' => Str::uuid()->toString(), 'referenceId' => $goal->id, 'entityType' => 'patientGoal', 'note' => $request->input('note'), 'flagId' => 1,
                'providerId' => $provider, 'providerLocationId' => $providerLocation, 'LocationEntityType' => $entityType
            ];
            $noteData = Note::create($note);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $noteData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($note), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Goal
    public function patientGoalDelete($request, $id, $goalId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientGoal::where('udid', $goalId)->update($input);
            $goal = Helper::tableName('App\Models\Patient\PatientGoal', $goalId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientGoals', 'tableId' => $goal, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            PatientGoal::where('udid', $goalId)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
