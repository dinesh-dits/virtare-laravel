<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterListQuestionOptionProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `listQuestionOption`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `listQuestionOption`(IN idx VARCHAR(50))
        BEGIN
        SELECT questionOptions.udid AS udid, questionOptions.options AS options,
        questionOptions.defaultOption AS defaultOption
        FROM questionOptions
        WHERE questionOptions.questionId = idx AND questionOptions.isDelete=0;
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
