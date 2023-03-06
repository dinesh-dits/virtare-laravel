<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskPerStaffProcedure extends Migration
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
            "CREATE PROCEDURE `taskPerStaff`(IN dueDate VARCHAR(50))
        BEGIN
        SELECT
        COUNT(taskAssignedTo.id) AS total,
        staffs.firstName AS text
        FROM taskAssignedTo
        JOIN staffs ON taskAssignedTo.assignedTo = staffs.id
        JOIN tasks ON tasks.id = taskAssignedTo.taskId
        WHERE taskAssignedTo.deletedAt IS NULL AND tasks.dueDate >= dueDate
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
