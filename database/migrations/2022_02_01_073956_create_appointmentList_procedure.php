<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `appointmentList`;
        CREATE PROCEDURE  appointmentList(fromDate VARCHAR(50),toDate VARCHAR(50)) 
        BEGIN
        SELECT  appointments.id as id,
                appointments.startDate as startDate,
                appointments.startTime as startTime,
                CONCAT(staffs.firstName,' ',staffs.lastName) as staff,
                CONCAT(patients.firstName,' ',patients.lastName) as patient
                FROM    appointments 
                JOIN staffs ON appointments.staffId = staffs.id
                JOIN patients ON appointments.patientId = patients.id
                WHERE   startDate >= fromDate AND startDate   <= toDate;
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
        Schema::dropIfExists('appointmentList_procedure');
    }
}
