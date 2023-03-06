<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQuestionnaireTemplateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `updateQuestionnaireTemplate`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `updateQuestionnaireTemplate`(IN data TEXT)
       BEGIN
        UPDATE
        questionnaireTemplates
                    SET
                    templateName =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.templateName")),
                    templateTypeId =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.templateTypeId")),
                    updatedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy"))
                    WHERE
                    questionnaireTemplates.udid = JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid"));
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
