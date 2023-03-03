<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CallPerStaffProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `callsPerStaff`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `callsPerStaff`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT
        COUNT(communicationCallRecords.referenceId) AS total,
        CONCAT(staffs.firstName, staffs.lastName) AS text
                FROM communicationCallRecords
                 JOIN staffs ON communicationCallRecords.referenceId = staffs.id
                 WHERE communicationCallRecords.createdAt >= fromDate AND communicationCallRecords.createdAt <= toDate AND communicationCallRecords.deletedAt IS NULL
            GROUP BY
    staffs.id;
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
