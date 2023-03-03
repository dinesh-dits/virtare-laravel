<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterdeleteQuestionnaireQuestionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `deleteQuestionnaireQuestion`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `deleteQuestionnaireQuestion`(IN data TEXT)
        BEGIN
        UPDATE
        questionnaireQuestions
                    SET
                    isActive =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isActive")),
                    isDelete =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isDelete")),
                    deletedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.deletedBy")),
                    deletedAt=CURRENT_TIMESTAMP
                    WHERE
                    questionnaireQuestions.questionnaireTempleteId =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionnaireTempleteId"));
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
