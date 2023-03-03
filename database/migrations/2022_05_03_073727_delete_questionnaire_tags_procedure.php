<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class DeleteQuestionnaireTagsProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `deleteQuestionnaireTags`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `deleteQuestionnaireTags`(IN data TEXT)
        BEGIN
        UPDATE
        tag
                    SET
                    isActive =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isActive")),
                    isDelete =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isDelete")),
                    deletedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.deletedBy")),
                    deletedAt=CURRENT_TIMESTAMP
                    WHERE
                    tag.referenceId =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.referenceId")) AND  tag.entity = JSON_UNQUOTE(JSON_EXTRACT(data, "$.entity"));
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
