<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGetPatientVitalByIdNewProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientVitalById`";
        DB::unprepared($procedure);

        $procedure = "CREATE PROCEDURE `getPatientVitalById`(patientVitalId INT)
        BEGIN
        SELECT patientVitals.*,
            vitalFields.name AS vitalField,
            patientVitals.value AS value,
            patientVitals.units AS units,
            patientVitals.takeTime AS takeTime,
            patientVitals.startTime AS startTime,
            patientVitals.endTime AS endTime,
            patientVitals.addType AS addType,
            globalCodes.name AS deviceType,
            patientVitals.createdType AS createdType,
            notes.note AS note,
            patientVitals.createdAt AS lastReadingDate,
            patientVitals.deviceInfo AS deviceInfo,
            patientVitals.flagId AS flagId,
            vitalFlags.name AS flagName,
            vitalFlags.color,
            vitalFlags.icon
            FROM patientVitals
            JOIN vitalFields ON patientVitals.vitalFieldId=vitalFields.id 
            JOIN vitalFlags ON patientVitals.flagId=  vitalFlags.id 
            left JOIN notes ON patientVitals.id=notes.referenceId
            JOIN globalCodes ON patientVitals.deviceTypeId=globalCodes.id
            WHERE patientVitals.id = patientVitalId
        ORDER BY patientVitals.takeTime DESC;
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
