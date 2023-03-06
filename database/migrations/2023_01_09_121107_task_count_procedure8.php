<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskCountProcedure8 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  taskCount(IN idx int,IN entity VARCHAR(50),IN fromDate VARCHAR(50),IN toDate VARCHAR(50),IN staffId INT) 
        BEGIN
        SELECT(COUNT(taskCategory.taskCategoryId)) AS total,
        globalCodes.name AS text,
        hour(tasks.dueDate) as time
        FROM
        tasks
        LEFT JOIN taskCategory
		ON taskCategory.taskId=tasks.id

        LEFT JOIN taskAssignedTo
		ON taskAssignedTo.taskId=tasks.id

        LEFT JOIN globalCodes
        ON globalCodes.id = taskCategory.taskCategoryId
        WHERE  `dueDate` between fromDate and toDate AND tasks.deletedAt IS NULL AND taskCategory.deletedAt IS NULL 
        AND (taskAssignedTo.assignedTo=staffId OR staffId='' AND taskAssignedTo.entityType='staff')
        GROUP BY
        taskCategory.taskCategoryId ;
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
