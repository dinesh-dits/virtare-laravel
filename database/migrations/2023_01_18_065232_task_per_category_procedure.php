<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskPerCategoryProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskPerCategory`;
        CREATE PROCEDURE `taskPerCategory`(IN dueDate VARCHAR(50))
        BEGIN
        SELECT
        COUNT(taskCategory.id) AS total, globalCodes.name AS text
        FROM taskCategory
        JOIN globalCodes ON taskCategory.taskCategoryId = globalCodes.id
        JOIN tasks ON tasks.id = taskCategory.taskId
        WHERE taskCategory.deletedAt IS NULL AND tasks.dueDate >= dueDate
        GROUP BY taskCategory.taskCategoryId;
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
