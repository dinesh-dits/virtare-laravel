<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcedureForCommunicationTypeCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `communicationTypeCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE `communicationTypeCount`(In date DATE)
        BEGIN
        Select count(communications.id) AS count,communications.createdat as duration,hour(communications.createdat) AS time, globalCodes.name AS messageName
        FROM `communications` 
            JOIN globalCodes 
        ON communications.messageTypeId  = globalCodes.id 
        WHERE date(`communications`.`createdat`) = date
        AND
        `communications`.`deletedat` IS NULL GROUP BY hour(communications.createdat),globalCodes.name;
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
        Schema::dropIfExists('communicationTypeCount_procedure');
    }
}
