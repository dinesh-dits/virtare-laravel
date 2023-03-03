<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientFlagCountProcedure extends Migration
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
        flags.name AS text
        FROM
        patientFlags
		LEFT JOIN flags
		ON flags.id=patientFlags.flagId
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
