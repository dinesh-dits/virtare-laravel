<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterCommunicationTypeCountProcedure extends Migration
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
        Select count(communications.id) AS count,communications.createdAt as duration,hour(communications.createdAt) AS time, globalCodes.name AS messageName
        FROM `communications` 
            JOIN globalCodes 
        ON communications.messageTypeId  = globalCodes.id 
        WHERE date(`communications`.`createdAt`) = date AND ((messageTypeId = 102 AND exists (select * from `messages` where `communications`.`id` = `messages`.`communicationId` and `messages`.`deletedAt` is null)) OR messageTypeId != 102)
        AND
        `communications`.`deletedat` IS NULL GROUP BY hour(communications.createdAt),globalCodes.name;
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
        Schema::dropIfExists('communication_type_count_procedure');
    }
}
