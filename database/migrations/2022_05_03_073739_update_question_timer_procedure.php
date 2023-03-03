<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQuestionTimerProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `updateQuestionTimer`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `updateQuestionTimer`(IN data TEXT)
       BEGIN
        UPDATE
        questionTimers
                    SET
                    duration =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.duration")),
                    updatedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy"))
                    WHERE
                    questionTimers.referenceId =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.referenceId")) AND  questionTimers.entity =JSON_UNQUOTE(JSON_EXTRACT(data, "$.entity"));
                    END;';
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
