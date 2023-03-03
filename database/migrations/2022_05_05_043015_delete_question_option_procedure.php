<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteQuestionOptionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `deleteQuestionOption`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `deleteQuestionOption`(IN data TEXT)
        BEGIN
        UPDATE
        questionOptions
                    SET
                    isActive =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isActive")),
                    isDelete =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isDelete")),
                    deletedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.deletedBy")),
                    deletedAt=CURRENT_TIMESTAMP
                    WHERE
                    questionOptions.questionId =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionId"));
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
