<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureAddWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `addWorkflow`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  addWorkflow(IN data JSON) 
        BEGIN
        INSERT INTO workFlow 
        (udid,providerId,workFlowTitle,startDate,endDate,eventId,status,createdBy,createdAt,description) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.title")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.startDate")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.endDate")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.eventId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.status")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdAt")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.description")));
        SELECT * from workFlow where workflowId = LAST_INSERT_ID() ;
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
        Schema::dropIfExists('addWorkflow');
    }
}
