<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetPatientConditionsCountProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionsCount`;
        CREATE PROCEDURE `getPatientConditionsCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
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
        LEFT JOIN flags ON patientFlags.flagId = flags.id 
        LEFT JOIN patients ON patients.flagId = patientFlags.patientId  
        WHERE
        (patientFlags.createdAt BETWEEN fromDate AND toDate)  AND patientFlags.deletedAt IS NULL AND patients.deletedAt IS NULL
        GROUP BY
        patientFlags.flagId;
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
