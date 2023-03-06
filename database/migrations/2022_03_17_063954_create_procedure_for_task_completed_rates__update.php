<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcedureForTaskCompletedRatesUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `taskCompletedRates`;
        CREATE PROCEDURE `taskCompletedRates`()
        BEGIN
        Select round(((SELECT count(*) as complete
        FROM
        tasks
        WHERE tasks.deletedAt IS NULL
        AND tasks.taskStatusId = 63 
        AND tasks.startDate <= CURDATE())/(SELECT count(*) as total
        FROM tasks
        WHERE
        tasks.deletedAt IS NULL
        AND startDate <= CURDATE())) * 100) as taskCompletionRates;
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
        Schema::dropIfExists('taskCompletedRates_procedure');
    }
}
