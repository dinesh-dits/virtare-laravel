<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetActivePatientCountProcedure3 extends Migration
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
        "CREATE PROCEDURE `getActivePatientCount`(IN idx INT,IN providerId INT, IN providerLocationId INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'Active Patients' AS text,
        '#267DFF' AS color,
        'Type' AS type,
        '#FFFFFF' AS textColor
        FROM patients 
        WHERE (patients.id=idx OR idx='') AND patients.isActive=1 AND patients.deletedAt IS NULL
        AND (patients.providerId=providerId OR providerId='') AND (patients.providerLocationId=providerLocationId OR providerLocationId='')
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
