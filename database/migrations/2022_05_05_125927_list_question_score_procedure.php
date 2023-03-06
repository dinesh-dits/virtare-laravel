<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ListQuestionScoreProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `listQuestionScore`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `listQuestionScore`(IN idx VARCHAR(50),IN entity VARCHAR(50))
        BEGIN
        SELECT questionScores.udid AS udid, questionScores.score AS score,
        questionScores.referenceId AS referenceId, questionScores.entityType
        FROM questionScores
        WHERE questionScores.referenceId = idx AND questionScores.entityType=entity AND questionScores.isDelete=0;
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
