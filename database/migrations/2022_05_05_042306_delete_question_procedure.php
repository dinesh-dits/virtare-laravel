<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteQuestionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `deleteQuestion`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `deleteQuestion`(IN data TEXT)
        BEGIN
        UPDATE
        questions
                    SET
                    isActive =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isActive")),
                    isDelete =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.isDelete")),
                    deletedBy =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.deletedBy")),
                    deletedAt=CURRENT_TIMESTAMP
                    WHERE
                    questions.udid =  JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid"));
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
