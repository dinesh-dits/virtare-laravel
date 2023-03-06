<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppointmentListProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `appointmentList`";

        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE  appointmentList(fromDate VARCHAR(50),toDate VARCHAR(50),staffIdx VARCHAR(50),IN statusId INT,IN providerId INT,IN providerLocationId INT)
        BEGIN
        SELECT *, appointments.id as id,
        appointments.startDateTime as startDate,
        appointments.udid as udid,
        notes.note AS note,
        appointmentType.name as appointmentType,
        globalCodes.name as duration,
        appointments.startDateTime as startTime,
        appointments.conferenceId,
        staffs.udid as staff_id,
        patients.udid as patient_id,
        flags.color as flags,
        flags.name as flagName,
        CONCAT(staffs.lastName,","," ",staffs.firstName," ",staffs.middleName) as staff,
        CONCAT(patients.lastName,","," ",patients.firstName," ",patients.middleName) as patient
        FROM    appointments
        JOIN staffs ON appointments.staffId = staffs.id
        LEFT JOIN notes ON appointments.id = notes.referenceId AND notes.entityType = "appointment"
        JOIN patients ON appointments.patientId = patients.id
        JOIN globalCodes ON appointments.durationId = globalCodes.id
        LEFT JOIN flags ON notes.flagId = flags.id
        JOIN globalCodes as appointmentType ON appointments.appointmentTypeId = appointmentType.id
        WHERE  (appointments.statusId=statusId OR statusId="")  AND (startDateTime >= fromDate OR fromDate = "") AND (startDateTime <= toDate  OR toDate="") AND (appointments.providerId=providerId OR providerId="") AND (appointments.providerLocationId=providerLocationId OR providerLocationId="") AND (staffId IN (SELECT * FROM JSON_TABLE( staffIdx, "$[*]" COLUMNS( Value INT PATH "$" ) ) as s) OR staffIdx = "" ) AND appointments.deletedAt IS NULL Group By appointments.id
        ORDER BY startDateTime DESC;
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
        //
    }
}
