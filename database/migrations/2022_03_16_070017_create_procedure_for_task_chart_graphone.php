<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcedureForTaskChartGraphone extends Migration
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
        tasks.createdAt as duration,
        hour(tasks.createdAt) as time,
        IF( COUNT(tasks.id) = 0,'#8E60FF','#8E60FF') AS color,
        IF( COUNT(tasks.id) = 0,'Total Tasks','Total Tasks') AS text
        FROM tasks
        WHERE tasks.deletedAt IS NULL AND createdAt > concat(curdate(),' 00:00:00') AND createdAt < concat(curdate(),' 23:59:59')
        UNION
        SELECT(IF((tasks.createdAt IS NULL),
                    0,
                    COUNT(tasks.id)
                )
            ) AS total,
            tasks.createdAt as duration,
            hour(tasks.createdAt) as time,
            IF(globalCodes.id = 61,'#267DFF',(IF(globalCodes.id = 62,'#FF6061','#62CFD7'))) AS color,
            globalCodes.name AS text
        FROM
            tasks
        RIGHT JOIN globalCodes ON tasks.taskStatusId = globalCodes.id
        WHERE
            globalCodes.globalCodeCategoryId = 5 AND tasks.deletedAt IS NULL
            AND tasks.createdAt > concat(curdate(),' 00:00:00') AND tasks.createdAt < concat(curdate(),' 23:59:59')
        GROUP BY
            globalCodes.id;
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
