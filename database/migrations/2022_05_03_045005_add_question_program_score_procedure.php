<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionProgramScoreProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addQuestionProgramScore`";
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `addQuestionProgramScore`(IN data TEXT)
        BEGIN
        INSERT INTO questionsProgramScoring
        (udid,questionOptionId,programId,score,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionOptionId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.programId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.score")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM questionsProgramScoring WHERE questionsProgramScoringId =LAST_INSERT_ID();  
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
