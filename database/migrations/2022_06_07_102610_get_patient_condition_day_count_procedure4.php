<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetPatientConditionDayCountProcedure4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionDayCount`;
        CREATE PROCEDURE `getPatientConditionDayCount`()
        BEGIN
        SELECT(COUNT(patientFlags.flagId)) AS total,
        flags.color as color,
        flags.name AS text,
        '#FFFFFF' as textColor,
        flags.id AS flagId
        FROM
        patientFlags
        LEFT JOIN flags ON patientFlags.flagId = flags.id  
        LEFT JOIN globalCodes ON globalCodes.id=flags.type 
        LEFT JOIN patients ON patients.id = patientFlags.patientId  
        WHERE
        (globalCodes.name='Patient' OR globalCodes.name='Both') AND patientFlags.deletedAt IS NULL AND patients.deletedAt IS NULL
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
