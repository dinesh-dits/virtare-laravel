<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureDeleteWorkflowSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `deleteWorkflowSteps`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  deleteWorkflowSteps(IN id int,IN data JSON) 
        BEGIN
        UPDATE `workFlowSteps` SET `isActive`="0",`isDelete`="1",`deletedBy`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.deletedBy")),`deletedAt`=JSON_UNQUOTE(JSON_EXTRACT(data, "$.deletedAt")) WHERE `udid` = id and deletedAt IS NULL;
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
        Schema::dropIfExists('deleteWorkflowSteps');
    }
}
