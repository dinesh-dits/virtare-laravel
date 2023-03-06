<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGetPatientsCount extends Migration
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
            "CREATE PROCEDURE `getPatientsCount`()
            BEGIN
        SELECT
        COUNT(patients.id) AS total,
        IF(patients.isActive = 1,'activePatients','inactivePatients') AS text,
        IF(patients.isActive = 1,'#0FB5C2','#267DFF') AS color,
        IF(patients.isActive = 1,'#FFFFFF','#FFFFFF') AS textColor
        FROM patients GROUP BY isActive;
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
