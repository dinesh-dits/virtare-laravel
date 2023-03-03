<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CallsPerStaffProcedure extends Migration
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
        COUNT(callRecords.staffId) AS total,
        CONCAT(staffs.firstName,' ', staffs.lastName) AS text,
        staffs.udid AS staffId
                FROM callRecords
                 JOIN staffs ON callRecords.staffId = staffs.id
                 WHERE callRecords.createdAt >= fromDate AND callRecords.createdAt <= toDate AND callRecords.deletedAt IS NULL
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
