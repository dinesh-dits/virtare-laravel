<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ListQuestionnaireTagProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `listQuestionnaireTag`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `listQuestionnaireTag`(IN reference INT,IN entity INT,IN search VARCHAR(50))
        BEGIN
        SELECT questionnaireTags.questionnaireTagId AS questionnaireTagId,questionnaireTags.udid AS udid, questionnaireTags.entityType AS entityType,
        questionnaireTags.referenceId AS referenceId
        FROM questionnaireTags
        WHERE questionnaireTags.referenceId = reference AND questionnaireTags.entityType = entity AND questionnaireTags.isDelete=0 AND
        (questionnaireTags.tag LIKE CONCAT('%',search,'%'));
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
