<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureAddWorkflowSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `addWorkflowSteps`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  addWorkflowSteps(IN data JSON) 
        BEGIN
        INSERT INTO workFlowSteps 
        (udid,providerId,workFlowId,stepTitle,createdBy,createdAt) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.workflowId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.title")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdAt")));
        SELECT * from workFlowSteps where workFlowStepId = LAST_INSERT_ID() ;
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
        Schema::dropIfExists('addWorkflowSteps');
    }
}
