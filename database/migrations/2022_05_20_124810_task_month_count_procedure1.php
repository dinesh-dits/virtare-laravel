<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskMonthCountProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskMonthCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  taskMonthCount(IN idx int,IN entity VARCHAR(50),IN fromDate VARCHAR(50),IN toDate VARCHAR(50)) 
        BEGIN
        SELECT(COUNT(taskCategory.taskCategoryId)) AS total,
        globalCodes.name AS text,
        DATE_FORMAT(tasks.startDate,'%b %d,%Y') as time
        FROM
        tasks
		LEFT JOIN taskAssignedTo
		ON taskAssignedTo.assignedTo=tasks.id
        LEFT JOIN taskCategory
		ON taskCategory.taskId=tasks.id
        LEFT JOIN globalCodes
        ON globalCodes.id = taskCategory.taskCategoryId
        WHERE (taskAssignedTo.assignedTo=idx OR idx='') AND (taskAssignedTo.entityType=entity OR entity='') 
        AND tasks.startDate >= fromDate AND tasks.startDate <= toDate AND tasks.deletedAt IS NULL AND taskAssignedTo.deletedAt IS NULL AND taskCategory.deletedAt IS NULL
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
