<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeleteQuestionProgramProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `deleteQuestionOptionPrograms`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `deleteQuestionOptionPrograms`(IN data TEXT)
        BEGIN
        UPDATE
        questionOptionPrograms
                    SET
                    isActive =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isActive")),
                    isDelete =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isDelete")),
                    deletedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.deletedBy")),
                    deletedAt=CURRENT_TIMESTAMP
                    WHERE
                    questionOptionPrograms.questionId =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionId"));
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
        Schema::dropIfExists('delete_question_program_procedure');
    }
}
