<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLastConversationMessageProcedure extends Migration
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
        SELECT communicationMessages.message FROM
        communications
        LEFT JOIN communicationMessages
        ON communicationMessages.communicationId=communications.id
        WHERE communications.referenceId=patientIdx
        AND communications.entityType='patient'
        ORDER BY communicationMessages.createdAt DESC;
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
        Schema::dropIfExists('last_conversation_message_procedure');
    }
}
