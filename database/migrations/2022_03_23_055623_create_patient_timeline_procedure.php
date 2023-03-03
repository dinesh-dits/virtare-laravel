<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientTimelineProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalPatientSummaryCount`";
        DB::unprepared($procedure);

        
        $procedure = "CREATE PROCEDURE `getTotalPatientSummaryCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT count(*) as total,
            patients.createdAt as duration,
            hour(patients.createdAt) as time
            FROM patients
            WHERE patients.createdAt >= fromDate AND patients.createdAt <= toDate AND patients.deletedAt IS NULL
            GROUP BY (time);
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
        Schema::dropIfExists('patient_timeline_procedure');
    }
}