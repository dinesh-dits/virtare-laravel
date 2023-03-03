<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AlterlistQuestionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `listQuestion`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `listQuestion`(IN idx VARCHAR(50))
        BEGIN
        SELECT * FROM `questions` WHERE (questions.udid=idx OR idx='') AND questions.isDelete=0 ORDER by questionId DESC;
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
