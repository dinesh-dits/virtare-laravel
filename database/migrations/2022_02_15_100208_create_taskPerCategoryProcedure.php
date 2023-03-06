<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskPerCategoryProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskPerCategory`;
        CREATE PROCEDURE `taskPerCategory`()
        BEGIN
        SELECT
        COUNT(taskCategory.id) AS total,
        globalCodes.name AS text
        FROM taskCategory
    JOIN globalCodes ON taskCategory.taskCategoryId = globalCodes.id
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
        Schema::dropIfExists('task_per_category_procedure');
    }
}
