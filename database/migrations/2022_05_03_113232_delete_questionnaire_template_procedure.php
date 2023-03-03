<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteQuestionnaireTemplateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `deleteQuestionnaireTemplate`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `deleteQuestionnaireTemplate`(IN data TEXT)
       BEGIN
        UPDATE
        questionnaireTemplates
                    SET
                    isActive =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.question")),
                    isDelete =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.dataTypeId")),
                    deletedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.updatedBy")),
                    deletedAt=CURRENT_TIMESTAMP
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
