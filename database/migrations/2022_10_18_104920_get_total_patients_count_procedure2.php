<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetTotalPatientsCountProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalPatientsCount`;
        CREATE PROCEDURE `getTotalPatientsCount`(IN idx INT,IN providerId INT, IN providerLocationId INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'Total Patients' AS text,
        'Type' AS type,
        '#FFFFFF' AS color,
        '#111111' AS textColor
        FROM patients
        WHERE (patients.id=idx OR idx='') AND patients.deletedAt IS NULL
        AND (patients.providerId=providerId OR providerId='') AND (patients.providerLocationId=providerLocationId OR providerLocationId='');
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
