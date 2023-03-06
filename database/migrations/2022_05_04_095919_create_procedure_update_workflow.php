<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureUpdateWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `updateWorkflow`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  updateWorkflow(IN id TEXT,IN data JSON) 
        BEGIN
        UPDATE `workFlow` SET `workFlowTitle`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.title")),`description`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.description")),`startDate`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.startDate")),`endDate`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.endDate")),`updatedBy`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy")),`updatedAt`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedAt")) WHERE `udid` = id AND workFlow.deletedAt IS NULL;
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
        Schema::dropIfExists('updateWorkflow');
    }
}
