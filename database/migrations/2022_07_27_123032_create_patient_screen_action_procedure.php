<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientScreenActionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createPatientScreenAction = "DROP PROCEDURE IF EXISTS `createPatientScreenAction`;";
        DB::unprepared($createPatientScreenAction);
        $createPatientScreenAction =
            "CREATE PROCEDURE  createPatientScreenAction(IN providerId bigInt,IN udid varchar(255), IN userId int,IN patientId int,IN actionId int) 
        BEGIN
        INSERT INTO patientActions (providerId,udid,userId,patientId,actionId) 
        values(providerId,udid,userId,patientId,actionId);
        END;";
        DB::unprepared($createPatientScreenAction);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_screen_action_procedure');
    }
}
