<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaskStatusCountUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskStatusCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `taskStatusCount`(IN fromDate VARCHAR(50),IN toDate VARCHAR(50))
        BEGIN
        SELECT(IF((tasks.createdAt IS NULL),0,COUNT(tasks.id))) AS total,
        IF(globalCodes.id = 61,'#267DFF',(IF(globalCodes.id = 62,'#FF6061','#62CFD7'))) AS color,
        globalCodes.name AS text
        FROM tasks
        RIGHT JOIN globalCodes ON tasks.taskStatusId = globalCodes.id
        WHERE globalCodes.globalCodeCategoryId = 5 AND tasks.deletedAt IS NULL AND tasks.dueDate >= fromDate AND tasks.dueDate <= toDate
        GROUP BY globalCodes.id;
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
