<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientFlagCountProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `patientFlagCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  patientFlagCount()
        BEGIN
        SELECT(COUNT(patientFlags.flagId)) AS total,
        flags.name AS text,
        flags.color AS color
        FROM
        patientFlags
		LEFT JOIN flags
		ON flags.id=patientFlags.flagId
        WHERE patientFlags.isDelete=0
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