<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListQuestionProcedure extends Migration
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
        SELECT * FROM `questions` WHERE (questions.udid=idx OR idx='') ORDER by questionId DESC;
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
        Schema::dropIfExists('list_question_procedure');
    }
}
