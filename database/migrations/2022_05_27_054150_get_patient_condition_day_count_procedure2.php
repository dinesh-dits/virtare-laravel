<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetPatientConditionDayCountProcedure2 extends Migration
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
        RIGHT JOIN flags ON patientFlags.flagId = flags.id  
        WHERE
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
        //
    }
}
