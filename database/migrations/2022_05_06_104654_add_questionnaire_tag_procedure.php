<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionnaireTagProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addQuestionnaireTags`";
        DB::unprepared($procedure);
        $procedure =
        'CREATE PROCEDURE `addQuestionnaireTags`(IN data TEXT)
        BEGIN
        INSERT INTO tag
        (udid,tag,entityType,referenceId,providerId,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.tag")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.entityType")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.referenceId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM tag WHERE tagId =LAST_INSERT_ID();  
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
