<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionnaireTemplateProviderProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addQuestionnaireTemplateProvider`";
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `addQuestionnaireTemplateProvider`(IN data TEXT)
        BEGIN
        INSERT INTO questionnaireTemplateProviders
        (udid,questionnaireTemplateId,providerId,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionnaireTemplateId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM questionnaireTemplateProviders WHERE questionnaireTemplateProviderId =LAST_INSERT_ID();  
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
