<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetNewPatientStaffCountPrcedure extends Migration
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
            "CREATE PROCEDURE `getNewPatientStaffCount`(fromDate VARCHAR(50),toDate VARCHAR(50),IN staffIdx INT)
        BEGIN
        SELECT
                COUNT(patients.id) AS total,
                'New' AS text,
                '#8E60FF' AS color,
                '#FFFFFF' AS textColor
                FROM patients
        LEFT JOIN patientStaffs ON patientStaffs.patientId= patients.id 
            WHERE
            patients.createdAt >= fromDate AND patients.createdAt <= toDate AND patients.deletedAt IS NULL 
            AND patientStaffs.staffId=staffIdx;
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
