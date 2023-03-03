<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConditionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionsCount`;
        CREATE PROCEDURE `getPatientConditionsCount`(timelineId INT(20))
        BEGIN
        IF timelineId = 122 THEN
            SELECT
            COUNT(patientFlags.id) AS total,
            flags.name as text,
            flags.color as color,
            '#FFFFFF' as textColor,
            patientFlags.flagId as flagId
                FROM
            patientFlags
            JOIN flags ON patientFlags.flagId = flags.id
            WHERE patientFlags.createdAt > date_sub(now(), interval 1 day)
            GROUP BY
            flagId,text,color;
        ELSEIF timelineId = 123 THEN
           SELECT
           COUNT(patientFlags.id) AS total,
            flags.name as text,
            flags.color as color,
            '#FFFFFF' as textColor,
            patientFlags.flagId as flagId
            FROM
            patientFlags
            JOIN flags ON patientFlags.flagId = flags.id
            WHERE patientFlags.createdAt > date_sub(now(), interval 1 week)
            GROUP BY
            flagId,text,color;
        ELSEIF timelineId = 124 THEN
            SELECT
            COUNT(patientFlags.id) AS total,
            flags.name as text,
            flags.color as color,
            '#FFFFFF' as textColor,
            patientFlags.flagId as flagId
            FROM
            patientFlags
            JOIN flags ON patientFlags.flagId = flags.id
            WHERE patientFlags.createdAt > date_sub(now(), interval 1 month)
            GROUP BY
            flagId,text,color;
        ELSEIF timelineId = 125 THEN
            SELECT
            COUNT(patientFlags.id) AS total,
            flags.name as text,
            flags.color as color,
            '#FFFFFF' as textColor,
            patientFlags.flagId as flagId
            FROM
            patientFlags
            JOIN flags ON patientFlags.flagId = flags.id
            WHERE patientFlags.createdAt > date_sub(now(), interval 1 year)
            GROUP BY
            flagId,text,color;
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
        Schema::dropIfExists('condition_procedure');
    }
}
