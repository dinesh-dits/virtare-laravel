<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureWorkFlowEventAction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getWorkflowEventAction`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  getWorkflowEventAction(IN eventId varchar(255)) 
        BEGIN
         SELECT globalCodes.name , globalCodes.id FROM `workFlowEventActions` INNER JOIN globalCodes on globalCodes.id = workFlowEventActions.workFlowActionId Inner Join workflowEvents on workflowEvents.workflowEventId = workFlowEventActions.workflowEventId where (workflowEvents.udid = eventId OR eventId = "") AND (workFlowEventActions.deletedAt IS NULL) ;
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
        Schema::dropIfExists('getWorkflowEventAction');
    }
}
