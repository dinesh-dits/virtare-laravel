<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientConditionTimelineProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionsCount`";
        DB::unprepared($procedure);

        $procedure ="CREATE PROCEDURE `getPatientConditionsCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT(IF((patientFlags.createdAt IS NULL),
        0,
        COUNT(patientFlags.flagId)
            )
        ) AS total,
        flags.color as color,
        flags.name AS text,
        '#FFFFFF' as textColor,
        flags.id AS flagId
        FROM
        patientFlags
        RIGHT JOIN flags ON patientFlags.flagId = flags.id AND (patientFlags.createdAt BETWEEN fromDate AND toDate )
        WHERE flags.name != 'other' and 
        
        patientFlags.deletedAt IS NULL 
        GROUP BY
        flagId,text,color;
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
        Schema::dropIfExists('patient_condition_timeline_procedure');
    }
}