<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetActivePatientCountProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getActivePatientCount`';
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `getActivePatientCount`(IN idx INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'activePatients' AS text,
        '#267DFF' AS color,
        '#FFFFFF' AS textColor
        FROM patients 
        WHERE (patients.id=idx OR idx='') AND patients.isActive=1 AND patients.deletedAt IS NULL
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
        //
    }
}
