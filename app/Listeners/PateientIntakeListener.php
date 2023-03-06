<?php

namespace App\Listeners;

use App\Events\PateientIntakeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Workflow\workFlowQueue;
use App\Services\Api\WorkflowService;
use Carbon;

class PateientIntakeListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PateientIntakeEvent  $event
     * @return void
     */
    public function handle(PateientIntakeEvent $event)
    {
        $today = date('Y-m-d',time());
        $WorkflowService = new WorkflowService;
        $workFlows = \DB::select('SELECT workflowEvents.primaryTable as mainTable,workflowCriteria.criteria,workFlow.* FROM `workFlow` INNER JOIN workflowEvents on workflowEvents.workflowEventId = workFlow.eventId INNER JOIN workflowCriteria on workflowCriteria.workflowId = workFlow.workFlowId WHERE `eventId` = "1" AND workFlow.deletedAt IS NULL AND workflowEvents.eventTitle = "Intake" and workFlow.startDate <='. "'$today'".'and workFlow.endDate >='. "'$today'".';');        
       //print_r( $workFlows );
        if (count($workFlows) > 0) {
            foreach ($workFlows as $key => $workFlowStep) {
                $workFlowUdId = $workFlowStep->udid;
                $workFlow = \DB::select( // Get workflow
                    "CALL getWorkflow('" . $workFlowUdId . "')"
                );
                $workFlowId = $workFlow[0]->workflowId;
                $id = '';
                $workFlowStepAction = \DB::select( // Get workflow actions 
                    "CALL getWorkflowStepActions('" . $workFlowId . "','" . $id . "')"
                );

                if (count($workFlowStepAction) > 0) {
                    $query =  $workFlowStep->condition;
                    $query = str_replace('{id}', $event->patient->id, $query);
                    //echo $query;
                    $result =  \DB::select($query); // Check if conditions match
                    //print_r($result); 
                    if (isset($result[0]->id) && !empty($result[0]->id)) {
                        // Create recod for workflow Assign
                       
                        $assignId = $WorkflowService->AssignWorkFlow($result[0]->id,$result[0]->userId);                   
                        foreach ($workFlowStepAction as $key => $workFlowAction) { // Asign all workflow to patient
                            //print_r($workFlowAction);
                            $actionId = $workFlowAction->actionId;
                            switch ($actionId) {
                                case 410: // Custom Form
                                    $this->assignCustomForm($workFlowAction, $result[0]->id,$result[0]->userId,$assignId);
                                    break;
                                case 409: //Questionnaire
                
                                    break;
                                case 231: //Create Alert
                
                                    break;
                                case 230: //Create New Document
                
                                    break;
                                case 229: //Create Task
                
                                    break;
                                case 228: //Send Email
                
                                    break;
                            }
                        }
                    }
                }
            }
        }







      


        /* foreach ($workFlows as $workFlow) {
            $criteria = json_decode($workFlow->criteria);
            $sqlCondition = "Select * from ".$workFlow->mainTable;
            $condition = "";
            $join = "";
            foreach ($criteria as $key => $value) {
                $workflowColumns = \DB::select('SELECT * FROM `workflowEventsColumns`  WHERE `udid` = "'.$value->fieldId.'" AND workflowEventsColumns.deletedAt IS NULL;');
                $workflowOperator = \DB::select('SELECT operators.* FROM `dataTypeOperators` INNER join operators on operators.operatorsId = dataTypeOperators.operatorId  WHERE dataTypeOperators.`udid` = "'.$value->operator.'" AND dataTypeOperators.deletedAt IS NULL;');


                if(!is_array($value->value)){
                    $condition .= " AND ".$workflowColumns[0]->columnName ." ".$workflowOperator[0]->symbol." ".$value->value;
                }else{
                    $valueSelected = implode(",", $value->value);
                    $condition .= " AND ".$workflowColumns[0]->columnName ." IN (".$valueSelected.")";
                }
                if($workflowColumns[0]->tableName != $workFlow->mainTable){
                    $join .= " INNER JOIN ".$workflowColumns[0]->tableName." on ".$workflowColumns[0]->tableName.".".$workflowColumns[0]->columnName." = ".$workFlow->mainTable.".id";
                }

            }
            $sqlCondition .= $join." where ".$workFlow->mainTable.".id={id} ".$condition;
            print_r($sqlCondition);
        }*/
    }

    public function assignCustomForm($workflow, $patientId,$userId,$assignId)
    {
        $actionField = json_decode($workflow->actionField);       
        if($workflow->executionOffsetType == '+'){
            $ececutionTime = Carbon\Carbon::now()->addDays($workflow->executionOffsetDays);
        }else if($workflow->executionOffsetType == '-'){
            $ececutionTime = Carbon\Carbon::now()->subDays($workflow->executionOffsetDays);
        }

        $data['ececutionTime'] = $ececutionTime;
        $data['formUdId'] = $actionField[0]->fieldValue;
        $data['patientId'] = $patientId;
        $data['userId'] = $userId;
        $data['assignId'] = $assignId;

        $WorkflowService = new WorkflowService;
        $assignId = $WorkflowService->WorkFlowAction($data);   
    }
}
