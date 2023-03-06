<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureGetWorkflowSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         $procedure = 'DROP PROCEDURE IF EXISTS `getWorkflowStep`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  getWorkflowStep(IN workflowId int,IN Id int) 
        BEGIN
        SELECT * from workFlowSteps where (workFlowSteps.workflowId = workflowId OR workflowId = "") AND (workFlowSteps.udid = Id Or Id = "") AND deletedAt IS NULL ;
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
        Schema::dropIfExists('getWorkflowStep');
    }
}
