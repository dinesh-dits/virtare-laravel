<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddWorkflowStepAction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `addWorkflowStepAction`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  addWorkflowStepAction(IN data JSON) 
        BEGIN
DECLARE actionsFields, actionsField,actionFieldId VARCHAR(4000);
DECLARE i ,lastId INT DEFAULT 0;
        INSERT INTO workFlowStepActions 
        (udid,providerId,workflowId,workFlowActionId,executionOffsetType,executionOffsetDays,workFlowEventOffsetFieldId,createdBy,createdAt) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.workflowId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.workFlowActionId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.executionOffsetType")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.executionOffsetDays")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.workFlowEventOffsetFieldId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdAt")));
        SELECT data->"$.actionsField" INTO actionsFields;
        SET lastId =  LAST_INSERT_ID();
        WHILE i < JSON_LENGTH(actionsFields) DO
    SELECT JSON_EXTRACT(actionsFields,CONCAT("$[",i,"]")) INTO actionsField;
    SELECT workFlowActionFieldId INTO actionFieldId from workFlowActionFields  where udid = JSON_UNQUOTE(JSON_EXTRACT(actionsField, "$.id")) ;
    INSERT INTO `workFlowStepActionValues`( `udid`, `providerId`, `workFlowStepActionId`, `workFlowActionFieldsId`, `fieldValue`, `createdBy`, `createdAt`) 
        VALUES (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),lastId,actionFieldId,JSON_UNQUOTE(JSON_EXTRACT(actionsField, "$.value")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdAt")));
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
        Schema::dropIfExists('addWorkflowStepAction');
    }
}
