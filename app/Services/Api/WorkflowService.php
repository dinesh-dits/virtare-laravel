<?php

namespace App\Services\Api;

use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Transformers\WorkFlow\WorkFlowTransformer;
use App\Transformers\WorkFlow\WorkFlowStepTransformer;
use App\Transformers\WorkFlow\WorkFlowEventTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\WorkFlow\WorkFlowCriteriaTransformer;
use App\Transformers\WorkFlow\WorkFlowStepActionTransformer;
use App\Transformers\WorkFlow\WorkFlowActionFieldTransformer;
use App\Transformers\WorkFlow\WorkFlowEventActionTransformer;
use App\Transformers\WorkFlow\WorkFlowEventColumnTransformer;
use App\Transformers\WorkFlow\WorkFlowEventOffsetTransformer;
use App\Models\Workflow\WorkFlowQueue;
use App\Models\Workflow\WorkFlowQueueStepAction;
use Exception;
use PHPUnit\TextUI\Help;

class WorkflowService
{
    public function add($request)
    {
        try {
            $input = $request->only(['title', 'startDate', 'endDate', 'eventId', 'description']);

            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();

            $workFlowEvent = DB::select(
                "CALL getWorkflowEvent('','" . $input['eventId'] . "');"
            );
            $input['eventId'] = $workFlowEvent[0]->workflowEventId;
            $otherData = [
                'udid' => Str::uuid()->toString(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'createdBy' => Auth::id(),
                'status' => 1,
                'createdAt' => Carbon::now()->format('Y-m-d H:i:s'),
                'condition' => '   '
            ];
            $otherData['startDate'] = Helper::dateOnly($input['startDate']);
            if (!empty($input['endDate'])) {

                $otherData['endDate'] = Helper::dateOnly($input['endDate']);
            } else {
                $otherData['endDate'] = NULL;
            }
            $data = json_encode(array_merge($input, $otherData));
            $workFlow = DB::select(
                "CALL addWorkflow('" . $data . "')"
            );
            return response()->json(['message' => trans('messages.createdSuccesfully'), 'data' => ['id' => $workFlow[0]->udid]], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function update($request, $id)
    {
        try {
            $input = $request->only(['title', 'startDate', 'endDate', 'eventId', 'description']);
            $otherData = [
                'updatedBy' => Auth::id(),
                'updatedAt' => Carbon::now(),
            ];


            $otherData['startDate'] = Helper::dateOnly($input['startDate']);
            if (!empty($input['endDate'])) {

                $otherData['endDate'] = Helper::dateOnly($input['endDate']);
            } else {
                $otherData['endDate'] = NULL;
            }
            $data = json_encode(array_merge($input, $otherData));
            DB::select(
                "CALL updateWorkflow('" . $id . "','" . $data . "')"
            );

            $workFlow = DB::select(
                "CALL getWorkflow('" . $id . "')"
            );
            $workFlowDetail = fractal()->item($workFlow[0])->transformWith(new WorkFlowTransformer())->toArray();
            return response()->json(array_merge(['message' => trans('messages.updatedSuccesfully')], $workFlowDetail), 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function get($request, $id)
    {
        try {
            $workFlow = DB::select(
                "CALL getWorkflow('" . $id . "')"
            );
            if ($id) {
                return fractal()->item($workFlow[0])->transformWith(new WorkFlowTransformer())->toArray();
            } else {

                $workFlow = $this->paginate($workFlow);
                return fractal()->collection($workFlow)->transformWith(new WorkFlowTransformer())->paginateWith(new IlluminatePaginatorAdapter($workFlow))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function events($request, $id)
    {
        try {
            $eventType = $request->input('eventType');
            if (!empty($eventType)) {

                //$eventTypeId = Helper::tableName('App\Models\GlobalCode\GlobalCode',$eventType);
                $eventTypeId = $eventType;
                $workFlowEvent = DB::select(
                    "CALL getWorkflowEvent('" . $eventTypeId . "','" . $id . "');"
                );
            } else {
                $workFlowEvent = DB::select(
                    "CALL getWorkflowEvent('','" . $id . "');"
                );
            }
            if ($id) {
                return fractal()->item($workFlowEvent[0])->transformWith(new WorkFlowEventTransformer())->toArray();
            } else {

                $workFlowEvent = $this->paginate($workFlowEvent);
                return fractal()->collection($workFlowEvent)->transformWith(new WorkFlowEventTransformer())->paginateWith(new IlluminatePaginatorAdapter($workFlowEvent))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function columns($request, $eventId)
    {
        try {
            $workFlowEventColumn = DB::select(
                "CALL getWorkflowEventColumn('" . $eventId . "');"
            );
            $workFlowEventColumn = $this->paginate($workFlowEventColumn);
            return fractal()->collection($workFlowEventColumn)->transformWith(new WorkFlowEventColumnTransformer())->paginateWith(new IlluminatePaginatorAdapter($workFlowEventColumn))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function action($request, $eventId)
    {
        try {
            $workFlowEventAction = DB::select(
                "CALL getWorkflowEventAction('" . $eventId . "');"
            );
            return fractal()->collection($workFlowEventAction)->transformWith(new WorkFlowEventActionTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function addCriteria($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $workFlow = DB::select(
                "CALL getWorkflow('" . $id . "')"
            );
            $workFlowId = $workFlow[0]->workflowId;
            $workflowCriteria = DB::select(
                "CALL getWorkflowCriteria('" . $workFlowId . "')"
            );
            $input = $request->only(['criteria']);
            $input['criteria'] = json_decode($input['criteria']);
            if (empty($workflowCriteria)) {

                $otherData = [
                    'udid' => Str::uuid()->toString(),
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation,
                    'workflowId' => $workFlowId,
                    'createdBy' => Auth::id(),
                    'createdAt' => Carbon::now()->format('Y-m-d H:i:s')
                ];
                $data = json_encode(array_merge($input, $otherData));
                $workFlowCriteria = DB::select(
                    "CALL addWorkflowCriteria('" . $data . "')"
                );
            } else {
                $otherData = [
                    'updatedBy' => Auth::id(),
                    'updatedAt' => Carbon::now()->format('Y-m-d H:i:s')
                ];
                $data = json_encode(array_merge($input, $otherData));
                $workFlowCriteria = DB::select(
                    "CALL updateWorkflowCriteria('" . $workFlowId . "','" . $data . "')"
                );
            }

            $workFlow = DB::select('SELECT workflowEvents.primaryTable as mainTable,workflowCriteria.criteria,workFlow.* FROM `workFlow` INNER JOIN workflowEvents on workflowEvents.workflowEventId = workFlow.eventId LEFT JOIN workflowCriteria on workflowCriteria.workflowId = workFlow.workFlowId WHERE workFlow.`workflowId` = "' . $workFlowId . '" AND workFlow.deletedAt IS NULL;');
            $criteria = $input['criteria'];
            $sqlCondition = "Select * from " . $workFlow[0]->mainTable;
            $condition = "";
            $join = "";
            $joinTable = array();
            foreach ($criteria as $key => $value) {
                $workflowColumns = DB::select('SELECT * FROM `workflowEventsColumns`  WHERE `udid` = "' . $value->fieldId . '" AND workflowEventsColumns.deletedAt IS NULL;');
                $workflowOperator = DB::select('SELECT operators.* FROM `dataTypeOperators` INNER join operators on operators.operatorsId = dataTypeOperators.operatorId  WHERE dataTypeOperators.`udid` = "' . $value->operator . '" AND dataTypeOperators.deletedAt IS NULL;');

                if ($workflowColumns[0]->columnName == "dob") {
                    if (!is_array($value->value)) {
                        $condition .= " AND (TIMESTAMPDIFF(YEAR,dob,CURDATE())) " . $workflowOperator[0]->symbol . " " . $value->value;
                    } else {
                        $valueSelected = implode(",", $value->value);
                        $condition .= " AND (TIMESTAMPDIFF(YEAR,dob,CURDATE())) " . $workflowOperator[0]->symbol . " (" . $valueSelected . ")";
                    }
                }

                if (!is_array($value->value)) {
                    if ($workflowColumns[0]->columnName != "dob")
                        $condition .= " AND " . $workflowColumns[0]->columnName . " " . $workflowOperator[0]->symbol . " " . $value->value;
                } else {
                    $valueSelected = implode(",", $value->value);
                    if ($workflowColumns[0]->columnName != "dob")
                        $condition .= " AND " . $workflowColumns[0]->columnName . " " . $workflowOperator[0]->symbol . " (" . $valueSelected . ")";
                }
                if ($workflowColumns[0]->tableName != $workFlow[0]->mainTable && !in_array($workflowColumns[0]->tableName, $joinTable)) {
                    array_push($joinTable, $workflowColumns[0]->tableName);
                    $join .= " INNER JOIN " . $workflowColumns[0]->tableName . " on " . $workflowColumns[0]->tableName . "." . $workflowColumns[0]->columnName . " = " . $workFlow[0]->mainTable . ".id";
                }
            }
            $sqlCondition .= $join . " where " . $workFlow[0]->mainTable . ".id={id} " . $condition;

            DB::statement("UPDATE `workFlow` SET `condition`='" . $sqlCondition . "',`updatedBy`='" . Auth::id() . "',`updatedAt`='" . Carbon::now() . "' where workflowId = '" . $workFlowId . "'");

            return response()->json(['message' => trans('messages.updatedSuccesfully'), 'data' => ['id' => $workFlowCriteria[0]->udid]], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getCriteria($request, $id)
    {
        try {
            $workFlow = DB::select(
                "CALL getWorkflow('" . $id . "')"
            );
            $workFlowId = $workFlow[0]->workflowId;
            $workflowCriteria = DB::select(
                "CALL getWorkflowCriteria('" . $workFlowId . "')"
            );
            if (!empty($workflowCriteria)) {

                return fractal()->item($workflowCriteria[0])->transformWith(new WorkFlowCriteriaTransformer())->toArray();
            } else {

                return json_encode(array("data" => new \stdClass()));
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function addStep($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $workFlow = DB::select(
                "CALL getWorkflow('" . $id . "')"
            );
            $workFlowId = $workFlow[0]->workflowId;
            $input = $request->only(['title']);
            $otherData = [
                'udid' => Str::uuid()->toString(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'workflowId' => $workFlowId,
                'createdBy' => Auth::id(),
                'createdAt' => Carbon::now()
            ];
            $data = json_encode(array_merge($input, $otherData));
            $workflowStep = DB::select(
                "CALL addWorkflowSteps('" . $data . "')"
            );
            return response()->json(['message' => trans('messages.createdSuccesfully'), 'data' => ['id' => $workflowStep[0]->udid]], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getStep($request, $workFlowId, $id)
    {
        try {
            $workFlow = DB::select(
                "CALL getWorkflow('" . $workFlowId . "')"
            );
            $workFlowId = $workFlow[0]->workflowId;
            $workFlowStep = DB::select(
                "CALL getWorkflowStep('" . $workFlowId . "','" . $id . "')"
            );
            if ($id) {
                return fractal()->item($workFlowStep[0])->transformWith(new WorkFlowStepTransformer())->toArray();
            } else {

                $workFlowStep = $this->paginate($workFlowStep);
                return fractal()->collection($workFlowStep)->transformWith(new WorkFlowStepTransformer())->paginateWith(new IlluminatePaginatorAdapter($workFlowStep))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateStep($request, $workFlowId, $id)
    {
        try {
            $workFlow = DB::select(
                "CALL getWorkflow('" . $workFlowId . "')"
            );
            $workFlowId = $workFlow[0]->workflowId;
            $input = $request->only(['title']);
            $otherData = [
                'updatedBy' => Auth::id(),
                'updatedAt' => Carbon::now()
            ];
            $data = json_encode(array_merge($input, $otherData));
            DB::select(
                "CALL updateWorkflowSteps('" . $id . "','" . $data . "')"
            );
            $workFlowStep = DB::select(
                "CALL getWorkflowStep('" . $workFlowId . "','" . $id . "')"
            );
            $workFlowDetail = fractal()->item($workFlowStep[0])->transformWith(new WorkFlowStepTransformer())->toArray();
            return response()->json(array_merge(['message' => trans('messages.updatedSuccesfully')], $workFlowDetail), 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteStep($request, $workFlowId, $id)
    {
        try {
            $data = [
                'deletedBy' => Auth::id(),
                'deletedAt' => Carbon::now()
            ];
            $data = json_encode($data);
            DB::select(
                "CALL deleteWorkflowSteps('" . $id . "','" . $data . "')"
            );
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function offset($request, $workFlowId, $id)
    {
        try {
            $workFlowEventOffset = DB::select(
                "CALL getEventOffset('" . $workFlowId . "','" . $id . "')"
            );
            return fractal()->collection($workFlowEventOffset)->transformWith(new WorkFlowEventOffsetTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function alertField($request, $workFlowId, $id)
    {
        try {
            $workFlowEventAlertField = DB::select(
                "CALL GetActionField('" . $id . "')"
            );
            return fractal()->collection($workFlowEventAlertField)->transformWith(new WorkFlowActionFieldTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function addStepAction($request, $workFlowId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = $request->only(['workFlowActionId', 'executionOffsetType', 'executionOffsetDays', 'workFlowEventOffsetFieldId', 'actionsField']);

            $workFlow = DB::select(
                "CALL getWorkflow('" . $workFlowId . "')"
            );
            $workFlowIdFinal = $workFlow[0]->workflowId;


            $workFlowEventOffset = DB::select(
                "CALL getEventOffset('" . $workFlowId . "','" . $input['workFlowEventOffsetFieldId'] . "')"
            );
            $input['workFlowEventOffsetFieldId'] = $workFlowEventOffset[0]->workflowEventsOffsetFieldId;

            if (!is_array($input['actionsField'])) {
                $input['actionsField'] = json_decode($input['actionsField']);
            }
            $otherData = [
                'udid' => Str::uuid()->toString(),
                'workflowId' => $workFlowIdFinal,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'createdBy' => Auth::id(),
                'createdAt' => Carbon::now()->format('Y-m-d H:i:s')
            ];
            $data = json_encode(array_merge($input, $otherData));

            $workFlow = DB::select(
                "CALL addWorkflowStepAction('" . $data . "')"
            );
            return response()->json(['message' => trans('messages.createdSuccesfully'), 'data' => ['id' => $workFlow[0]->udid]], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateStepAction($request, $workFlowId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = $request->only(['workFlowActionId', 'executionOffsetType', 'executionOffsetDays', 'workFlowEventOffsetFieldId', 'actionsField']);

            $workFlow = DB::select(
                "CALL getWorkflow('" . $workFlowId . "')"
            );
            $workFlowIdFinal = $workFlow[0]->workflowId;


            $workFlowEventOffset = DB::select(
                "CALL getEventOffset('" . $workFlowId . "','" . $input['workFlowEventOffsetFieldId'] . "')"
            );
            $input['workFlowEventOffsetFieldId'] = $workFlowEventOffset[0]->workflowEventsOffsetFieldId;

            if (!is_array($input['actionsField'])) {
                $input['actionsField'] = json_decode($input['actionsField']);
            }
            $otherData = [
                'udid' => $id,
                'updatedBy' => Auth::id(),
                'updatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            $data = json_encode(array_merge($input, $otherData));

            $workFlow = DB::select(
                "CALL updateWorkflowStepAction('" . $data . "')"
            );
            return response()->json(['message' => trans('messages.updatedSuccesfully'), 'data' => ['id' => $workFlow[0]->udid]], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getStepAction($request, $workFlowId, $id)
    {
        try {
            $workFlow = DB::select(
                "CALL getWorkflow('" . $workFlowId . "')"
            );
            $workFlowId = $workFlow[0]->workflowId;


            $workFlowStepAction = DB::select(
                "CALL getWorkflowStepActions('" . $workFlowId . "','" . $id . "')"
            );
            if ($id) {
                return fractal()->item($workFlowStepAction[0])->transformWith(new WorkFlowStepActionTransformer())->toArray();
            } else {
                $workFlowStepAction = $this->paginate($workFlowStepAction);
                return fractal()->collection($workFlowStepAction)->transformWith(new WorkFlowStepActionTransformer())->paginateWith(new IlluminatePaginatorAdapter($workFlowStepAction))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteStepAction($request, $workFlowId, $id)
    {
        try {
            $patient = \App\Models\Patient\Patient::where('id', 82)->first();

            // \Event::dispatch(new \App\Events\PateientIntakeEvent($patient));
            // dd($patient);

            $workFlowStepAction = DB::select(
                "CALL deleteWorkflowStepActions('" . Auth::id() . "','" . $id . "')"
            );
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function paginate($items, $perPage = 20, $page = null, $options = [])
    {
        try {
            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
            $items = $items instanceof Collection ? $items : Collection::make($items);
            return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function AssignWorkFlow($workFlowId, $userId)
    {
        try {
            // echo 'herere';
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = [
                'udid' => Str::uuid()->toString(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'createdBy' => Auth::id(),
                'status' => 1,
                'workFlowId' => $workFlowId,
                'keyId' => $userId
            ];

            $workFlowQueue = WorkFlowQueue::create($data);
            DB::commit();
            return $workFlowQueue->id;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function WorkFlowAction($inputData)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = [
                'udid' => Str::uuid()->toString(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'createdBy' => Auth::id(),
                'workFlowQueueStepId' => $inputData['assignId'],
                'actionScript' => json_encode(array('action' => 'assignCustomForm', 'patientId' => $inputData['patientId'], 'user_id' => $inputData['userId'], 'assignedBy' => Auth::id(), 'formUdId' => $inputData['formUdId'])),//'assignCustomForm('.$inputData['formUdId'].')',
                'executionDateTime' => $inputData['ececutionTime']
            ];
            WorkFlowQueueStepAction::create($data);

        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assign_workflow()
    {
        try {
            $pendingFlows = WorkFlowQueueStepAction::whereDate('executionDateTime', '<=', date('Y-m-d'))->where('assignStatus', 0)->get();
            if ($pendingFlows->count() > 0) {
                foreach ($pendingFlows as $key => $flow) {
                    /*  echo $fnmae= $flow->actionScript;
                        $helper = new Helper();
                        print_r($helper);
                        $providerLocation = $helper->$fnmae;*/

                    //
                    $actions = json_decode($flow->actionScript);
                    //  print_r( $actions);
                    if ($actions->action == 'assignCustomForm') {
                        $assignId = Helper::assignCustomForm($actions->formUdId, $actions->user_id);
                        WorkFlowQueueStepAction::where('workFlowQueueStepActionId', $flow->workFlowQueueStepActionId)->update(['customFormAssignedId' => $assignId, 'assignStatus' => 1]);
                    }
                    //  WorkFlowQueueStepAction::where('workFlowQueueStepActionId',$flow->workFlowQueueStepActionId)->update(['customFormAssignedId'=> $assignId,'assignStatus'=>1]);
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage() . $e->getLine();
        }
    }
}
