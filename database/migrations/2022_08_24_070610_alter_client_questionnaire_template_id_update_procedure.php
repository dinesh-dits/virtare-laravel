<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientQuestionnaireTemplateIdUpdateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addClientQuestionScore`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `addClientQuestionScore`(IN data TEXT)
        BEGIN
        INSERT INTO clientQuestionScore
        (udid,clientQuestionnaireTemplateId,score,dataType,referenceId,entityType,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.clientQuestionnaireTemplateId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.score")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.dataType")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.referenceId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.entityType")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM clientQuestionScore WHERE clientQuestionScoreId =LAST_INSERT_ID();  
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
