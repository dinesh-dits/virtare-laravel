<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddPatientScreenActionProcedure extends Migration
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
       "CREATE PROCEDURE  createPatientScreenAction(IN udid varchar(255), IN userId int,IN patientId int,IN actionId int) 
        BEGIN
        INSERT INTO patientActions (udid,userId,patientId,actionId) 
        values(udid,userId,patientId,actionId);
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
        Schema::dropIfExists('add_patient_screen_action_procedure');
    }
}
