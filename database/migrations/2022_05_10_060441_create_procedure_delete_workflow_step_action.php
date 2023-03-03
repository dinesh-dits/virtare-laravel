<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureDeleteWorkflowStepAction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `deleteWorkflowStepActions`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  deleteWorkflowStepActions(IN userId int,IN id varchar(255)) 
        BEGIN
            DECLARE actionId INT DEFAULT 0;
            UPDATE `workFlowStepActions` SET `isActive`="0",`isDelete`="1",`deletedBy`= userId,`deletedAt`=now() WHERE `udid` = id ;
            select workFlowStepActionId INTO actionId from workFlowStepActions WHERE `udid` = id ;
            UPDATE `workFlowStepActionValues` SET `isActive`="0",`isDelete`="1",`deletedBy`= userId,`deletedAt`=now() WHERE `workFlowStepActionId` = actionId ;
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
        Schema::dropIfExists('deleteWorkflowStepActions');
    }
}
