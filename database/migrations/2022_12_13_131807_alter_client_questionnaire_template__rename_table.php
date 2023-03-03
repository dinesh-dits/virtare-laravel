<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClientQuestionnaireTemplateRenameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('clientQuestionnaireTemplates', 'clientFillUpQuestionnaire');
        Schema::rename('clientQuestionResponses', 'clientFillUpQuestionnaireQuestions');

        Schema::table('clientFillUpQuestionnaire', function (Blueprint $table) {
            $table->renameColumn('clientQuestionnaireTemplateId', 'clientFillUpQuestionnaireId');
        });
        
        Schema::table('clientFillUpQuestionnaireQuestions', function (Blueprint $table) {
            $table->renameColumn('clientQuestionnaireTemplateId', 'clientFillUpQuestionnaireId');
            $table->renameColumn('clientQuestionResponseId', 'clientFillupQuestionnaireQuestionId');
        });
        
        Schema::table('clientQuestionScore', function (Blueprint $table) {
            $table->renameColumn('clientQuestionnaireTemplateId', 'clientFillUpQuestionnaireId');
        });

        Schema::table('clientResponsePrograms', function (Blueprint $table) {
            $table->renameColumn('clientQuestionnaireTemplateId', 'clientFillUpQuestionnaireId');
        });

        Schema::table('clientResponseAnswer', function (Blueprint $table) {
            $table->renameColumn('clientQuestionnaireTemplateId', 'clientFillUpQuestionnaireId');
            $table->renameColumn('cleintQuestionResponseId', 'clientFillupQuestionnaireQuestionId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::rename('old_table_name', 'new_table_name');
    }
}
