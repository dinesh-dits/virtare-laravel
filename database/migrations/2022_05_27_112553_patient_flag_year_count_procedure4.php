<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientFlagYearCountProcedure4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `patientFlagYearCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  patientFlagYearCount(IN patientIdx INT,IN fromDate VARCHAR(50),IN toDate VARCHAR(50))
        BEGIN
        SELECT(COUNT(patientFlags.flagId)) AS total,
        flags.name AS text,
        flags.color AS color,
        MONTHNAME(patientFlags.createdAt) as time
        FROM
        patientFlags
		LEFT JOIN flags
		ON flags.id=patientFlags.flagId
        WHERE (patientFlags.patientId=patientIdx OR patientIdx='') AND patientFlags.createdAt >= fromDate AND patientFlags.createdAt <= toDate 
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
