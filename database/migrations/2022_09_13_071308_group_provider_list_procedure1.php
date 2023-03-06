<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupProviderListProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `groupProviderList`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `groupProviderList`(IN Idx INT,IN providerId INT,IN providerLocationId INT)
        BEGIN
        SELECT  groupProviders.groupProviderId AS groupProviderId,groupProviders.udid AS udid,providers.name,providers.udid AS providerUdid
        FROM groupProviders 
        JOIN providers ON groupProviders.providerId =  providers.id
        WHERE
        groupProviders.groupId = Idx AND groupProviders.deletedAt IS NULL AND providers.deletedAt IS NULL
        AND (groupProviders.providerId=providerId OR providerId='') AND (groupProviders.providerLocationId=providerLocationId OR providerLocationId='')
;
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
