<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientFlagDayStaffCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `patientFlagDayStaffCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  patientFlagDayStaffCount(IN patientIdx INT,IN staffIdx INT,IN providerId INT, IN providerLocationId INT)
        BEGIN
        SELECT(COUNT(patientFlags.flagId)) AS total,
        flags.name AS text,
        flags.color AS color
        FROM
        patientFlags
		LEFT JOIN flags ON flags.id=patientFlags.flagId
        LEFT JOIN globalCodes ON globalCodes.id=flags.type 
        LEFT JOIN patients ON patients.id=patientFlags.patientId
        LEFT JOIN patientStaffs ON patientStaffs.patientId= patients.id 
        WHERE (globalCodes.name='Patient' OR globalCodes.name='Both') AND patientFlags.deletedAt IS NULL 
        AND flags.deletedAt IS NULL AND patients.deletedAt IS NULL AND patientStaffs.staffId=staffIdx
        AND (patientFlags.providerId=providerId OR providerId='') AND (patientFlags.providerLocationId=providerLocationId OR providerLocationId='')
        GROUP BY
        patientFlags.flagId 
        ORDER BY flags.id ASC;
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
