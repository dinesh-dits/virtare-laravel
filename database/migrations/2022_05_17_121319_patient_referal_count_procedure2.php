<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientReferalCountProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `referalCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  referalCount() 
        BEGIN
        SELECT(COUNT(patientReferals.name)) AS total,
        patientReferals.name AS text
        FROM
        patientReferals
        GROUP BY
        patientReferals.name;
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
