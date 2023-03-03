<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixListQuestionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `listQuestion`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `listQuestion`(IN idx VARCHAR(50),IN search VARCHAR(50))
        BEGIN
        SELECT questions.question AS question,questions.udid,questions.questionId,questions.dataTypeId,globalCodes.name,questions.isActive AS isActive
        FROM `questions`
        LEFT JOIN globalCodes
        ON globalCodes.id = questions.dataTypeId
         WHERE (questions.udid=idx OR idx='') AND questions.isDelete=0 AND
        ((globalCodes.name LIKE CONCAT('%',search,'%'))
        OR (questions.question LIKE CONCAT('%',search,'%')))
        ORDER by questions.createdAt DESC;
        END;";
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
