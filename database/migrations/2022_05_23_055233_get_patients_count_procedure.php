<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetPatientsCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getPatientsCount`';
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `getPatientsCount`(IN idx INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        IF(patients.isActive = 1,'activePatients','inactivePatients') AS text,
        IF(patients.isActive = 1,'#0FB5C2','#267DFF') AS color,
        IF(patients.isActive = 1,'#FFFFFF','#FFFFFF') AS textColor
        FROM patients 
        WHERE (patients.id=idx OR idx='') AND patients.deletedAt IS NULL
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
