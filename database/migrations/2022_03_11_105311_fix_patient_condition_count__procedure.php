<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixPatientConditionCountProcedure extends Migration
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
        
        $procedure =
            "CREATE PROCEDURE `getPatientConditionsCount`(timelineId INT(20))
        BEGIN
        IF timelineId = 122 THEN
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
            RIGHT JOIN flags ON patientFlags.flagId = flags.id AND patientFlags.createdAt > date_sub(now(), interval 1 day)
            WHERE
            patientFlags.deletedAt IS NULL 
            GROUP BY
            flagId,text,color;
        ELSEIF timelineId = 123 THEN
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
            RIGHT JOIN flags ON patientFlags.flagId = flags.id AND patientFlags.createdAt > date_sub(now(), interval 1 week)
            WHERE
            patientFlags.deletedAt IS NULL 
            GROUP BY
            flagId,text,color;
        ELSEIF timelineId = 124 THEN
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
            RIGHT JOIN flags ON patientFlags.flagId = flags.id AND patientFlags.createdAt > date_sub(now(), interval 1 month)
            WHERE
            patientFlags.deletedAt IS NULL 
            GROUP BY
            flagId,text,color;
        ELSEIF timelineId = 125 THEN
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
            RIGHT JOIN flags ON patientFlags.flagId = flags.id AND patientFlags.createdAt > date_sub(now(), interval 1 year)
            WHERE
            patientFlags.deletedAt IS NULL 
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
        //
    }
}
