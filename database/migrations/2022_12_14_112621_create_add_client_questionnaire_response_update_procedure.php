<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddClientQuestionnaireResponseUpdateProcedure extends Migration
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
        INSERT INTO clientFillUpQuestionnaireQuestions
        (udid,clientFillUpQuestionnaireId,questionnaireQuestionId,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.clientFillUpQuestionnaireId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionnaireQuestionId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM clientFillUpQuestionnaireQuestions WHERE clientFillupQuestionnaireQuestionId =LAST_INSERT_ID();  
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
        // Schema::dropIfExists('add_client_questionnaire_response_update_procedure');
    }
}
