<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentExistForPatientProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `appointmentExistForPatient`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `appointmentExistForPatient`(patientIdx INT,startDateTimex TIMESTAMP)
    BEGIN
    SELECT EXISTS(SELECT appointments.* FROM appointments
    WHERE
       patientId = patientIdx AND (appointments.startDateTime >= startDateTimex AND appointments.startDateTime <= DATE_ADD(startDateTimex, INTERVAL 10 MINUTE))) as isExist;
    END;";
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointment_exist_for_patient_procedure');
    }
}
