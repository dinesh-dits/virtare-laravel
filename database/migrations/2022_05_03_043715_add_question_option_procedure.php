<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestionOptionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addQuestionOption`";
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `addQuestionOption`(IN data TEXT)
        BEGIN
        INSERT INTO questionOptions
        (udid,options,questionId,score,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.options")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.questionId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.score")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM questionOptions WHERE questionOptionId =LAST_INSERT_ID();  
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
