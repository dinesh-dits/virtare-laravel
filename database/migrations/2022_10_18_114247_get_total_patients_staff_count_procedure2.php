<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetTotalPatientsStaffCountProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalPatientsStaffCount`;
        CREATE PROCEDURE `getTotalPatientsStaffCount`(IN idx INT,IN staffIdx INT,IN providerId INT, IN providerLocationId INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'Total Patients' AS text,
        'Type' AS type,
        '#FFFFFF' AS color,
        '#111111' AS textColor
        FROM patients
        LEFT JOIN patientStaffs ON patientStaffs.patientId= patients.id 
        WHERE (patients.id=idx OR idx='') AND patients.deletedAt IS NULL AND patientStaffs.staffId=staffIdx
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
