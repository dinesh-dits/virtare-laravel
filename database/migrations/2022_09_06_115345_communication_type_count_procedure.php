<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CommunicationTypeCountProcedure extends Migration
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
        CREATE PROCEDURE `communicationTypeCount`(In date DATE,IN providerId INT,IN providerLocationId INT)
        BEGIN
        Select count(communications.id) AS count,communications.createdAt as duration,hour(communications.createdAt) AS time, globalCodes.name AS messageName
        FROM `communications` 
            JOIN globalCodes 
        ON communications.messageTypeId  = globalCodes.id 
        WHERE date(`communications`.`createdAt`) = date AND ((messageTypeId = 102 AND exists (select * from `messages` where `communications`.`id` = `messages`.`communicationId` and `messages`.`deletedAt` is null)) OR messageTypeId != 102)
        AND (communications.providerId=providerId OR providerId='') AND (communications.providerLocationId=providerLocationId OR providerLocationId='')
        AND `communications`.`deletedat` IS NULL GROUP BY hour(communications.createdAt),globalCodes.name;
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
