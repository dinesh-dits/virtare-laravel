<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LastConversationMessageProcedure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `lastConversationMessage`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE `lastConversationMessage`(In patientIdx INT)
        BEGIN
        SELECT messages.message FROM
        communications
        LEFT JOIN messages
        ON messages.communicationId=communications.id
        WHERE (communications.referenceId=patientIdx) OR (communications.from=patientIdx)
        ORDER BY messages.createdAt DESC;
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
