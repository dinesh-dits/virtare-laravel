<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProceduregetWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         $procedure = 'DROP PROCEDURE IF EXISTS `getWorkflow`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  getWorkflow(IN workflowId varchar(255)) 
        BEGIN
        SELECT workFlow.*,workflowEvents.eventTitle,workflowEvents.udid as eventId  from workFlow inner join workflowEvents on workflowEvents.workflowEventId = workFlow.eventId where workFlow.udid = workflowId OR workflowId = "" AND (workflowEvents.deletedAt IS NULL) AND (workFlow.deletedAt IS NULL);
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
        Schema::dropIfExists('getWorkflow');
    }
}
