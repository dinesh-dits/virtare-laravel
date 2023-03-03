<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetInActivePatientCountProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getInActivePatientCount`';
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `getInActivePatientCount`(IN idx INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'inactivePatients' AS text,
        '#0FB5C2' AS color,
        '#FFFFFF' AS textColor
        FROM patients 
        WHERE (patients.id=idx OR idx='') AND patients.isActive=0 AND patients.deletedAt IS NULL
        GROUP BY isActive;
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
        Schema::table('active_patient_count_procedure2', function (Blueprint $table) {
            //
        });
    }
}
