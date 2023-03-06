<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTaskPerStaffProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskPerStaff`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `taskPerStaff`()
        BEGIN
        SELECT
        COUNT(taskAssignedTo.id) AS total,
        staffs.firstName AS text
        FROM taskAssignedTo
    JOIN staffs ON taskAssignedTo.assignedTo = staffs.id
    WHERE taskAssignedTo.deletedAt IS NULL
    GROUP BY taskAssignedTo.assignedTo;
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
