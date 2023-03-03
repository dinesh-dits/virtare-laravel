<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddQuestionTimerProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addQuestionTimer`";
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `addQuestionTimer`(IN data TEXT)
        BEGIN
        INSERT INTO questionTimers
        (udid,duration,entityType,referenceId,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.duration")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.entityType")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.referenceId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM questionTimers WHERE questionTimerId =LAST_INSERT_ID();  
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
