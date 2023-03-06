<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProcedureWorngTimeStampAppointmentNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `appointmentListNotification`;";
        DB::unprepared($procedure);
        $procedure = "CREATE PROCEDURE  appointmentListNotification(fromDate VARCHAR(50),toDate VARCHAR(50)) 
        BEGIN
        SELECT appointments.id as id, appointments.startDateTime as startTime, staffs.id as staffId, patients.id as patientId, patients.userId as patientUserId, staffs.userId as staffUserId FROM appointments JOIN staffs ON appointments.staffId = staffs.id JOIN patients ON appointments.patientId = patients.id WHERE UNIX_TIMESTAMP(startDateTime) >= UNIX_TIMESTAMP(fromDate) AND UNIX_TIMESTAMP(startDateTime) <= UNIX_TIMESTAMP(toDate) AND appointments.id NOT IN (SELECT `appointmentId` FROM `appointmentNotification` ) AND appointments.deletedAt IS NULL ORDER BY startDateTime DESC;
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
        //
    }
}
