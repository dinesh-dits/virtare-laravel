<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotaltaskCountUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `totalTasksCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `totalTasksCount`(IN fromDate VARCHAR(50),IN toDate VARCHAR(50))
        BEGIN
            SELECT(IF((tasks.createdAt IS NULL),0,COUNT(tasks.id))) AS total,
            IF( COUNT(tasks.id) = 0,'#8E60FF','#8E60FF') AS color,
            IF( COUNT(tasks.id) = 0,'Total Tasks','Total Tasks') AS text
            FROM
            tasks where tasks.deletedAt IS NULL AND tasks.dueDate >= fromDate AND tasks.dueDate <= toDate;
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
