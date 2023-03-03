<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetActivePatientStaffCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `getActivePatientStaffCount`';
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `getActivePatientStaffCount`(IN idx INT,IN staffIdx INT)
        BEGIN
        SELECT
        COUNT(patients.id) AS total,
        'Active Patients' AS text,
        '#267DFF' AS color,
        '#FFFFFF' AS textColor
        FROM patients 
        LEFT JOIN patientStaffs ON patientStaffs.patientId= patients.id 
        WHERE (patients.id=idx OR idx='') AND patients.isActive=1 AND patients.deletedAt IS NULL 
        AND patientStaffs.staffId=staffIdx
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
        //
    }
}