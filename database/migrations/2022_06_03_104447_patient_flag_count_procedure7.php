<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientFlagCountProcedure7 extends Migration
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
        CREATE PROCEDURE  patientFlagCount(IN patientIdx INT,IN fromDate VARCHAR(50),IN toDate VARCHAR(50))
        BEGIN
        SELECT(COUNT(patientFlags.flagId)) AS total,
        flags.name AS text,
        flags.color AS color,
        hour(patientFlags.createdAt) as time
        FROM
        patientFlags
		LEFT JOIN flags ON flags.id=patientFlags.flagId
        LEFT JOIN globalCodes ON globalCodes.id=flags.type 
        LEFT JOIN patients ON patients.id=patientFlags.patientId
        WHERE globalCodes.name='Patient' OR globalCodes.name='Both' AND (patientFlags.patientId=patientIdx OR patientIdx='') AND patientFlags.createdAt >= fromDate AND patientFlags.createdAt <= toDate AND
        patientFlags.deletedAt IS NULL AND patients.deletedAt IS NULL
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
