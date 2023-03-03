<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixIssueAppointmentListProcedure extends Migration
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
        IF toDate = '' THEN
        SELECT  appointments.id as id,
                appointments.startDateTime as startDate,
                notes.note AS note,
                appointmentType.name as appointmentType,
                globalCodes.name as duration,
                appointments.startDateTime as startTime,
                staffs.udid as staff_id,
                patients.udid as patient_id,
                CONCAT(staffs.firstName,' ',staffs.lastName) as staff,
                CONCAT(patients.firstName,' ',patients.lastName) as patient
                FROM    appointments 
                JOIN staffs ON appointments.staffId = staffs.id
JOIN notes ON appointments.id = notes.referenceId
                JOIN patients ON appointments.patientId = patients.id
                JOIN globalCodes ON appointments.durationId = globalCodes.id
                JOIN globalCodes as appointmentType ON appointments.appointmentTypeId = appointmentType.id
                WHERE   startDateTime >= fromDate
                ORDER BY startDateTime DESC;
        ELSE 
        SELECT  appointments.id as id,
                appointments.startDateTime as startDate,
                 notes.note AS note,
                appointmentType.name as appointmentType,
                globalCodes.name as duration,
                appointments.startDateTime as startTime,
                staffs.udid as staff_id,
                patients.udid as patient_id,
                CONCAT(staffs.firstName,' ',staffs.lastName) as staff,
                CONCAT(patients.firstName,' ',patients.lastName) as patient
                FROM    appointments 
                JOIN staffs ON appointments.staffId = staffs.id
JOIN notes ON appointments.id = notes.referenceId
                JOIN patients ON appointments.patientId = patients.id
                JOIN globalCodes ON appointments.durationId = globalCodes.id
                JOIN globalCodes as appointmentType ON appointments.appointmentTypeId = appointmentType.id
                WHERE   startDateTime >= fromDate AND startDateTime <= toDate
                ORDER BY startDateTime DESC;
        END IF;
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
