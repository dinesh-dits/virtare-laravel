<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalPatientProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalPatientsCount`;
        CREATE PROCEDURE `getTotalPatientsCount`()
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'totalPatients' AS text,
        '#FFFFFF' AS color,
        '#111111' AS textColor
        FROM patients;
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
        Schema::dropIfExists('total_patient_procedure');
    }
}
