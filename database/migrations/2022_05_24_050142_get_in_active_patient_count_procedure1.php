<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetInActivePatientCountProcedure1 extends Migration
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
        WHERE (patients.id=idx OR idx='') AND patients.isActive=0
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
