<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskCountProcedure21 extends Migration
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
        CREATE PROCEDURE  taskCount(IN provider INT,IN providerLocation INT,IN entityType VARCHAR(50),IN fromDate VARCHAR(50),IN toDate VARCHAR(50),IN staffId INT,IN dueDate INT) 
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
        AND (tasks.providerId=provider OR provider='') AND (tasks.providerLocationId=providerLocation OR providerLocation='') AND (tasks.entityType=entityType OR entityType='')
        AND (taskAssignedTo.assignedTo=staffId OR staffId='' AND taskAssignedTo.entityType='staff') AND tasks.dueDate >= dueDate
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
