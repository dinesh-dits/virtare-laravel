<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrioritytasksProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskPriorityCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `taskPriorityCount`(IN fromDate VARCHAR(50),IN toDate VARCHAR(50))
        BEGIN
        SELECT(IF((tasks.createdAt IS NULL),0,COUNT(tasks.id))) AS total,
        IF(globalCodes.id = 70,'#E63049',(IF(globalCodes.id = 71,'#269B8F','#4690FF'))) AS color,
        globalCodes.name AS text
        FROM tasks
        RIGHT JOIN globalCodes ON tasks.priorityId = globalCodes.id
        WHERE 
        globalCodes.globalCodeCategoryId = 7 AND tasks.deletedAt IS NULL AND tasks.dueDate >= fromDate AND tasks.dueDate <= toDate
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
        Schema::dropIfExists('prioritytasks_procedure');
    }
}
