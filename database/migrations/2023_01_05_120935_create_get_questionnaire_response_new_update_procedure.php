<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetQuestionnaireResponseNewUpdateProcedure extends Migration
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
        SELECT cqt.clientFillUpQuestionnaireId,cqt.questionnaireTemplateId,cqt.referenceId,cqt.entityType,
        cqr.questionnaireQuestionId,cra.clientResponseAnswerId,cra.clientFillupQuestionnaireQuestionId,cra.dataType,cra.response
        FROM `clientFillUpQuestionnaire` as cqt
        LEFT JOIN clientFillUpQuestionnaireQuestions as cqr
        ON cqr.clientFillUpQuestionnaireId = cqt.clientFillUpQuestionnaireId
        LEFT JOIN clientResponseAnswer as cra
        ON cra.clientFillupQuestionnaireQuestionId = cqr.clientFillupQuestionnaireQuestionId
        WHERE cqt.clientFillUpQuestionnaireId = qTemplateId AND cqr.isActive = 1 AND cra.isActive = 1;
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
        // Schema::dropIfExists('get_questionnaire_response_new_update_procedure');
    }
}
