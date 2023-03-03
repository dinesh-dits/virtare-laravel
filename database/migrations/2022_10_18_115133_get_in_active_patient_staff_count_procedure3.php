<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetInActivePatientStaffCountProcedure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getInActivePatientStaffCount`';
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `getInActivePatientStaffCount`(IN idx INT,IN staffIdx INT,IN providerId INT, IN providerLocationId INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'Inactive Patients' AS text,
        '#0FB5C2' AS color,
        'Type' AS type,
        '#FFFFFF' AS textColor
        FROM patients 
        LEFT JOIN patientStaffs ON patientStaffs.patientId= patients.id 
        WHERE (patients.id=idx OR idx='') AND patients.isActive=0 AND patients.deletedAt IS NULL 
        AND patientStaffs.staffId=staffIdx
        AND (patients.providerId=providerId OR providerId='') AND (patients.providerLocationId=providerLocationId OR providerLocationId='')
        GROUP BY patients.isActive;
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
        Schema::table('active_patient_staff_count_procedure3', function (Blueprint $table) {
            //
        });
    }
}
