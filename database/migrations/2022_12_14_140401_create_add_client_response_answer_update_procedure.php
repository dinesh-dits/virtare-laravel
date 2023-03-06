<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddClientResponseAnswerUpdateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addClientResponseAnswer`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `addClientResponseAnswer`(IN data TEXT)
        BEGIN
        INSERT INTO clientResponseAnswer
        (udid,clientFillupQuestionnaireQuestionId,dataType,response,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.clientFillupQuestionnaireQuestionId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.dataType")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.response")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM clientResponseAnswer WHERE clientFillupQuestionnaireQuestionId =JSON_UNQUOTE(JSON_EXTRACT(data, "$.clientFillupQuestionnaireQuestionId"));  
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
        Schema::dropIfExists('add_client_response_answer_update_procedure');
    }
}
