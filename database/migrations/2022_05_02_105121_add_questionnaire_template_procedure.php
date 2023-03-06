<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionnaireTemplateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addQuestionnaireTemplate`";
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `addQuestionnaireTemplate`(IN data TEXT)
        BEGIN
        INSERT INTO questionnaireTemplates
        (udid,templateName,templateTypeId,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.templateName")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.templateTypeId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM questionnaireTemplates WHERE questionnaireTemplateId =LAST_INSERT_ID();  
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
