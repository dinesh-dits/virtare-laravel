<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetQuestionnaireResponseProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getQuestionnaireResponse`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `getQuestionnaireResponse`(IN qTemplateId INT)
        BEGIN
        SELECT cqt.clientQuestionnaireTemplateId,cqt.questionnaireTemplateId,cqt.referenceId,cqt.entityType,
        cqr.questionnaireQuestionId,cra.clientResponseAnswerId,cra.cleintQuestionResponseId,cra.dataType,cra.response
        FROM `clientQuestionnaireTemplates` as cqt
        LEFT JOIN clientQuestionResponses as cqr
        ON cqr.clientQuestionnaireTemplateId = cqt.clientQuestionnaireTemplateId
        LEFT JOIN clientResponseAnswer as cra
        ON cra.cleintQuestionResponseId = cqr.clientQuestionResponseId
        WHERE cqt.clientQuestionnaireTemplateId = qTemplateId;
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
        Schema::dropIfExists('get_questionnaire_response_procedure');
    }
}
