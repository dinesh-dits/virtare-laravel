<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientQuestionnaireResponseProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addClientQuestionnaireResponse`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `addClientQuestionnaireResponse`(IN data TEXT)
        BEGIN
        INSERT INTO clientQuestionResponses
        (udid,clientQuestionnaireTemplateId,questionnaireQuestionId,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.clientQuestionnaireTemplateId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionnaireQuestionId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM clientQuestionResponses WHERE clientQuestionResponseId =LAST_INSERT_ID();  
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
