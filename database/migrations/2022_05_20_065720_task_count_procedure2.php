<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskCountProcedure2 extends Migration
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
        CREATE PROCEDURE  taskCount(IN idx int,IN entity VARCHAR(50),IN fromDate VARCHAR(50),IN toDate VARCHAR(50)) 
        BEGIN
        SELECT(COUNT(tasks.taskTypeId)) AS total,
        globalCodes.name AS text,
        hour(tasks.startDate) as time
        FROM
        tasks
		LEFT JOIN taskAssignedTo
		ON taskAssignedTo.assignedTo=tasks.id
        LEFT JOIN globalCodes
        ON globalCodes.id = tasks.taskTypeId
        WHERE (taskAssignedTo.assignedTo=idx OR idx='') AND (taskAssignedTo.entityType=entity OR entity='') 
        AND tasks.startDate >= fromDate AND tasks.startDate <= toDate AND tasks.deletedAt IS NULL AND taskAssignedTo.deletedAt IS NULL
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
