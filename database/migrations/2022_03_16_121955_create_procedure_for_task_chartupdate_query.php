<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcedureForTaskChartupdateQuery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalTaskSummaryCountInGraph`;
        CREATE PROCEDURE `getTotalTaskSummaryCountInGraph`()
        BEGIN
        SELECT count(*) as total,
        tasks.startDate as duration,
        DAYNAME(tasks.startDate) as time,
        IF( COUNT(tasks.id) = 0,'#8E60FF','#8E60FF') AS color,
        IF( COUNT(tasks.id) = 0,'Total Tasks','Total Tasks') AS text
        FROM tasks
        WHERE
        tasks.deletedAt IS NULL AND startDate > date_sub(now(), interval 7 day) 
         AND startDate <= CURDATE() group by time
        UNION
        SELECT count(*) as total,
        tasks.startDate as duration,
        DAYNAME(tasks.startDate) as time,
        IF(globalCodes.id = 61,'#267DFF',(IF(globalCodes.id = 62,'#FF6061','#62CFD7'))) AS color,
        globalCodes.name AS text
        FROM
        tasks
        RIGHT JOIN globalCodes ON tasks.taskStatusId = globalCodes.id
        WHERE tasks.deletedAt IS NULL
        AND tasks.startDate > date_sub(now(), interval 7 day) 
        AND tasks.startDate <= CURDATE() group by globalCodes.id,time;
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
        Schema::dropIfExists('getTotalTaskSummaryCountInGraph_procedure');
    }
}
