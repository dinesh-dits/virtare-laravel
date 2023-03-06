<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NonComplianceProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `nonCompliance`';
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `nonCompliance`(IN fromDate VARCHAR(50), IN deviceTypeIdx INT)
        BEGIN
        SELECT patients.id FROM patients
        LEFT JOIN patientVitals
        ON patientVitals.patientId = patients.id
        WHERE patientVitals.takeTime <= fromDate AND patientVitals.deviceTypeId=deviceTypeIdx;
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
