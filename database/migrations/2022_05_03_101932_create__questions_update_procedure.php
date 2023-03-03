<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsUpdateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `UpdateQuestions`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `UpdateQuestions`(IN data TEXT)
       BEGIN
        UPDATE
        questions
                    SET
                    question =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.question")),
                    dataTypeId =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.dataTypeId")),
                    updatedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy"))
                    WHERE
                    questions.udid = JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid"));
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
        // Schema::dropIfExists('_questions_update_procedure');
    }
}
