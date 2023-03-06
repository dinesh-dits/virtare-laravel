<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientFlagDayCountProcedure5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `patientFlagDayCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  patientFlagDayCount(IN patientIdx INT)
        BEGIN
        SELECT(COUNT(patientFlags.flagId)) AS total,
        flags.name AS text,
        flags.color AS color
        FROM
        patientFlags
		LEFT JOIN flags ON flags.id=patientFlags.flagId
        LEFT JOIN globalCodes ON globalCodes.id=flags.type 
        LEFT JOIN patients ON patients.id=patientFlags.patientId
        WHERE globalCodes.name='Patient' OR globalCodes.name='Both' AND (patientFlags.patientId=patientIdx OR patientIdx='')  AND patientFlags.deletedAt IS NULL AND flags.deletedAt IS NULL AND patients.deletedAt IS NULL
        GROUP BY
        patientFlags.flagId ;
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
