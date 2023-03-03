<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskPriorityCountProcedure1 extends Migration
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
            "CREATE PROCEDURE `taskPriorityCount`(fromDate VARCHAR(50),toDate VARCHAR(50),IN providerId INT,IN providerLocationId INT)
        BEGIN
        SELECT(IF((tasks.createdAt IS NULL),
            0,
            COUNT(tasks.id)
        )
    ) AS total,
    IF(globalCodes.id = 70,'#E63049',(IF(globalCodes.id = 71,'#269B8F','#4690FF'))) AS color,
    globalCodes.name AS text
FROM
    tasks
RIGHT JOIN globalCodes ON tasks.priorityId = globalCodes.id
WHERE (tasks.dueDate>=fromDate OR fromDate='') AND (tasks.dueDate<=toDate OR toDate='') AND (tasks.providerId=providerId OR providerId='') AND (tasks.providerLocationId=providerLocationId OR providerLocationId='') AND
    globalCodes.globalCodeCategoryId = 7 AND tasks.deletedAt IS NULL
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
        //
    }
}
