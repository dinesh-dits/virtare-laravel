<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpdateWorkflowStepActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        $procedure = 'DROP PROCEDURE IF EXISTS `updateWorkflowStepAction`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  updateWorkflowStepAction(IN data JSON) 
        BEGIN
DECLARE actionsFields, actionsField,actionFieldId VARCHAR(4000);
DECLARE i ,lastId INT DEFAULT 0;
        UPDATE `workFlowStepActions` SET `workFlowActionId`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.workFlowActionId")),`executionOffsetType`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.executionOffsetType")),`executionOffsetDays`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.executionOffsetDays")),`workFlowEventOffsetFieldId`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.workFlowEventOffsetFieldId")),`updatedBy`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy")),`updatedAt`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedAt")) where udid = JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid"));
        SELECT data->"$.actionsField" INTO actionsFields;

        SELECT workFlowStepActionId INTO lastId from workFlowStepActions  where udid = JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")) ;

        WHILE i < JSON_LENGTH(actionsFields) DO
    SELECT JSON_EXTRACT(actionsFields,CONCAT("$[",i,"]")) INTO actionsField;


    SELECT workFlowActionFieldId INTO actionFieldId from workFlowActionFields  where udid = JSON_UNQUOTE(JSON_EXTRACT(actionsField, "$.id")) ;


    UPDATE `workFlowStepActionValues` SET `fieldValue`=JSON_UNQUOTE(JSON_EXTRACT(actionsField, "$.value")),`updatedBy`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy")),`updatedAt`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedAt")) WHERE `workFlowActionFieldsId`= actionFieldId AND `workFlowStepActionId` = lastId;

    
    SELECT i + 1 INTO i;
END WHILE;
        SELECT * from workFlowStepActions where workFlowStepActionId = lastId ;
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
        Schema::dropIfExists('update_workflow_step_actions');
    }
}
