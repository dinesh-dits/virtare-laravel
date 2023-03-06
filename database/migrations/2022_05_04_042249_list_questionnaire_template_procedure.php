<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ListQuestionnaireTemplateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `listQuestionnaireTemplate`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `listQuestionnaireTemplate`(IN idx VARCHAR(50))
       BEGIN
        SELECT questionnaireTemplates.udid AS udid, questionnaireTemplates.templateName AS templateName,
        globalCodes.name AS templateType, globalCodes.id AS templateTypeId
        FROM questionnaireTemplates
        LEFT JOIN globalCodes
        ON globalCodes.id = questionnaireTemplates.templateTypeId
        WHERE questionnaireTemplates.udid = idx OR idx='';
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
