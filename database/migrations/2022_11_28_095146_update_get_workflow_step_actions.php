<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGetWorkflowStepActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getWorkflowStepActions`;';

        DB::unprepared($procedure);

        $procedure = 'CREATE PROCEDURE  getWorkflowStepActions(IN workflowid varchar(255),IN id varchar(255)) 
        BEGIN
       SELECT workFlowStepActions.*,action.id as actionId,action.name as actionName,workflowEventsOffsetField.udid as eventOffsetId,workflowEventsOffsetField.displayName as columnName,(SELECT 
                  JSON_ARRAYAGG(
                      JSON_OBJECT(
                        "id", workFlowActionFields.udid, "fieldValue", workFlowStepActionValues.fieldValue
                      )
                    
                  ) 
                FROM 
                  workFlowStepActionValues
                INNER JOIN workFlowActionFields on workFlowActionFields.workFlowActionFieldId = workFlowStepActionValues.workFlowActionFieldsId
                WHERE 
                  workFlowStepActionValues.workFlowStepActionId = workFlowStepActions.workFlowStepActionId
                  AND workFlowStepActionValues.deletedAt IS NULL) as actionField

 FROM `workFlowStepActions`
INNER join globalCodes as `action` on `action`.id = workFlowStepActions.workFlowActionId
INNER JOIN  workflowEventsOffsetField on workflowEventsOffsetField.workflowEventsOffsetFieldId = workFlowStepActions.workFlowEventOffsetFieldId where (workFlowStepActions.udid = id OR id = "") AND workFlowStepActions.workflowId =workflowid AND workFlowStepActions.deletedAt IS NULL  ;
        END;';

        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
