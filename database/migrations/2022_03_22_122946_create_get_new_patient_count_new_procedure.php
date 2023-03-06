<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetNewPatientCountNewProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getNewPatientCountNew`;
        CREATE PROCEDURE `getNewPatientCountNew`(timelineStartDate INT(20),timelineEndDate INT(20))
        BEGIN
        SELECT
                COUNT(patients.id) AS total,
                'New' AS text,
                '#8E60FF' AS color,
                '#FFFFFF' AS textColor
                FROM patients
            WHERE
             (patients.createdAt BETWEEN FROM_UNIXTIME(timelineStartDate) AND FROM_UNIXTIME(timelineEndDate)) AND patients.deletedAt IS NULL ;
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
        Schema::dropIfExists('get_new_patient_count_new_procedure');
    }
}
