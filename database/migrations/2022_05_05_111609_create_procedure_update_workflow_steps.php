<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureUpdateWorkflowSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `updateWorkflowSteps`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  updateWorkflowSteps(IN id int,IN data JSON) 
        BEGIN
        UPDATE `workFlowSteps` SET `stepTitle`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.title")),`updatedBy`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy")),`updatedAt`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedAt")) WHERE `udid` = id and deletedAt IS NULL;
        SELECT * from workFlowSteps where udid = id AND workFlowSteps.deletedAt IS NULL;
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
        Schema::dropIfExists('updateWorkflowSteps');
    }
}
