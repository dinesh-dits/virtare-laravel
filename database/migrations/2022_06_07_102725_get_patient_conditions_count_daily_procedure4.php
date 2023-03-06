<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetPatientConditionsCountDailyProcedure4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionsCountDaily`;
        CREATE PROCEDURE `getPatientConditionsCountDaily`(fromDate VARCHAR(50),toDate VARCHAR(50))
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
        LEFT JOIN flags ON flags.id = patientFlags.flagId 
        LEFT JOIN globalCodes ON globalCodes.id=flags.type 
        LEFT JOIN patients ON patients.id = patientFlags.id 
        WHERE
        (globalCodes.name='Patient' OR globalCodes.name='Both') AND patientFlags.deletedAt IS NULL AND patients.deletedAt IS NULL 
        AND patientFlags.createdAt >= fromDate AND patientFlags.createdAt <= toDate 
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
        //
    }
}
