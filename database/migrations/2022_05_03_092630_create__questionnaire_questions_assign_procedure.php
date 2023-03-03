<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionnaireQuestionsAssignProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addQuestionnaireQuestions`";
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `addQuestionnaireQuestions`(IN data TEXT)
        BEGIN
        INSERT INTO questionnaireQuestions
        (udid,questionId,questionnaireTempleteId,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionnaireTempleteId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM questionnaireQuestions WHERE questionnaireQuestionId =LAST_INSERT_ID();  
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
        // Schema::dropIfExists('_questionnaire_questions_assign_procedure');
    }
}
