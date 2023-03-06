<?php

namespace App\Services\Api;

use App\Helper;
use App\Models\Task\PatientTask;
use App\Transformers\Task\PatientTaskTransformer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PatientTaskService
{

    // List Patient Task
    public function patientTaskList($request, $id, $patientTaskId)
    {
        try {
            $patientId = Helper::entity('patient', $id);
            if (!$patientTaskId) {
                $data = PatientTask::with('patient', 'priority', 'status')->where('patientId', $patientId)
                    ->leftJoin('globalCodes as g1', 'g1.id', '=', 'patientTasks.priorityId')
                    ->leftJoin('globalCodes as g2', 'g2.id', '=', 'patientTasks.statusId');
                if ($request->status) {
                    $data->where('g2.name', $request->status);
                }
                if ($request->priority) {
                    $data->where('g1.name', $request->priority);
                }
                // if ($request->fromStartTime && $request->toStartTime) {
                //     $fromTime = Helper::date($request->fromStartTime);
                //     $toTime  = Helper::date($request->toStartTime);
                //     $data->whereBetween('patientTasks.startTimeDate', [$fromTime, $toTime]);
                // }
                if ($request->fromStartTime) {
                    $fromTime = Helper::date($request->fromStartTime);
                    $data->where('patientTasks.startTimeDate', '>=', $fromTime);
                }
                if ($request->toStartTime) {
                    $toTime = Helper::date($request->toStartTime);
                    $data->where('patientTasks.startTimeDate', '<=', $toTime);
                }
                $data = $data->select('patientTasks.*')->get();

                return fractal()->collection($data)->transformWith(new PatientTaskTransformer())->toArray();
            } else {
                $patientTask = PatientTask::where('udid', $patientTaskId)->first();
                $data = PatientTask::where([['patientId', $patientId], ['patientTaskId', $patientTask->patientTaskId]])->first();
                return fractal()->item($data)->transformWith(new PatientTaskTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Task
    public function createPatientTask($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $patientId = Helper::entity('patient', $id);
            $input = [
                'udid' => Str::uuid()->toString(),
                'patientId' => $patientId,
                'title' => $request->input('title'),
                'priorityId' => $request->input('priority'),
                'statusId' => '274',
                'startTimeDate' => Helper::date($request->input('startTimeDate')),
                'dueDate' => Helper::date($request->input('dueDate')),
                'description' => $request->input('description'),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            PatientTask::create($input);
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Task
    public function patientTaskUpdate($request, $id, $patientTaskId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $patientId = Helper::entity('patient', $id);
            $patientTask = PatientTask::where('udid', $patientTaskId)->first();
            $input = array();
            if (!empty($request->input('title'))) {
                $input['title'] = $request->input('title');
            }
            if (!empty($request->input('priority'))) {
                $input['priorityId'] = $request->input('priority');
            }
            if (!empty($request->input('startTimeDate'))) {
                $input['startTimeDate'] = Helper::date($request->input('startTimeDate'));
            }
            if (!empty($request->input('dueDate'))) {
                $input['dueDate'] = Helper::date($request->input('dueDate'));
            }
            if (!empty($request->input('description'))) {
                $input['description'] = $request->input('description');
            }
            if (!empty($request->input('statusId'))) {
                $input['statusId'] = $request->input('statusId');
            }
            $input['updatedBy'] = Auth::id();
            $input['providerId'] = $provider;
            $input['providerLocationId'] = $providerLocation;
            if (!empty($input)) {
                PatientTask::where([['patientId', $patientId], ['patientTaskId', $patientTask->patientTaskId]])->update($input);
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Task
    public function deletePatientTask($request, $id, $patientTaskId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $patientId = Helper::entity('patient', $id);
            $patientTask = PatientTask::where('udid', $patientTaskId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientTask::where([['patientId', $patientId], ['patientTaskId', $patientTask->patientTaskId]])->update($input);
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
