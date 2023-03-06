<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskCountProcedure extends Migration
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
        CREATE PROCEDURE  taskCount(IN idx int,IN entity VARCHAR(50)) 
        BEGIN
        SELECT(COUNT(tasks.taskTypeId)) AS total,
        globalCodes.name AS text
        FROM
        tasks
		LEFT JOIN taskAssignedTo
		ON taskAssignedTo.assignedTo=tasks.id
        LEFT JOIN globalCodes
        ON globalCodes.id = tasks.taskTypeId
        WHERE (taskAssignedTo.assignedTo=idx OR idx='') AND taskAssignedTo.entityType=entity
        GROUP BY
        tasks.taskTypeId ;
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
