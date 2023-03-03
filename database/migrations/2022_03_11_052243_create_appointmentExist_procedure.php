<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentExistProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `appointmentExist`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `appointmentExist`(staffIdx INT,startDateTimex TIMESTAMP)
    BEGIN
    SELECT EXISTS(SELECT appointments.* FROM appointments
    WHERE
        staffId = staffIdx AND (appointments.startDateTime >= startDateTimex AND appointments.startDateTime <= DATE_ADD(startDateTimex, INTERVAL 10 MINUTE))) as isExist;
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
        Schema::dropIfExists('appointment_exist_procedure');
    }
}
