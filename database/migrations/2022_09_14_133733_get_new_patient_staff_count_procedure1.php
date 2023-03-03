<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetNewPatientStaffCountProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getNewPatientStaffCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `getNewPatientStaffCount`(fromDate VARCHAR(50),toDate VARCHAR(50),IN staffIdx INT,IN providerId INT, IN providerLocationId INT)
        BEGIN
        SELECT
                COUNT(patients.id) AS total,
                'New Patients' AS text,
                '#8E60FF' AS color,
                '#FFFFFF' AS textColor
                FROM patients
        LEFT JOIN patientStaffs ON patientStaffs.patientId= patients.id 
            WHERE
            patients.createdAt >= fromDate AND patients.createdAt <= toDate AND patients.deletedAt IS NULL 
            AND patientStaffs.staffId=staffIdx
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
