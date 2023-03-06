<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProcedureAppointmentConferenceIdUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         $procedure = "DROP PROCEDURE IF EXISTS `appointmentConferenceIdUpdate`;
        CREATE PROCEDURE `appointmentConferenceIdUpdate`()
        BEGIN
        UPDATE `appointments` SET `conferenceId`=null WHERE now() > DATE_ADD(`startDateTime`, INTERVAL (select minutes from durationIntervals where durationIntervals.durationId = appointments.durationId) MINUTE);
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
         Schema::dropIfExists('appointmentConferenceIdUpdate');
    }
}
