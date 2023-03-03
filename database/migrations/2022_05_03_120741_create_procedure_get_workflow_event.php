<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureGetWorkflowEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getWorkflowEvent`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  getWorkflowEvent(IN eventType varchar(255),IN eventId varchar(255)) 
        BEGIN
        SELECT * from workflowEvents where (udid = eventId OR eventId = "") AND (eventTypeId = eventType OR eventType = "") AND (workflowEvents.deletedAt IS NULL) ;
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
        Schema::dropIfExists('getWorkflowEvent');
    }
}
