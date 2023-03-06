<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProceduregetEventOffset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         $procedure = 'DROP PROCEDURE IF EXISTS `getEventOffset`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  getEventOffset(IN workflowId varchar(255),IN id varchar(255)) 
        BEGIN
        SELECT workflowEventsOffsetField.* FROM `workflowEventsOffsetField` inner join workFlow on workFlow.eventId = workflowEventsOffsetField.workflowEventId where (workFlow.udid = workflowId OR workflowId = "") AND (workflowEventsOffsetField.udid = id OR id = "") AND workflowEventsOffsetField.deletedAt IS NULL  AND workFlow.deletedAt IS NULL ;
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
        Schema::dropIfExists('getEventOffset');
    }
}
