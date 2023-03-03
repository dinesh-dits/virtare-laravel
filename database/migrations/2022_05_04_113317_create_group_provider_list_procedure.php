<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGroupProviderListProcedure extends Migration
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
            "CREATE PROCEDURE `groupProviderList`(IN Idx INT)
    BEGIN
    SELECT  groupProviders.groupProviderId AS groupProviderId,groupProviders.udid AS udid,providers.name
        FROM groupProviders 
        
        JOIN providers ON groupProviders.providerId =  providers.id
   WHERE
       groupProviders.groupId = Idx AND groupProviders.deletedAt IS NULL AND providers.deletedAt IS NULL;
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
        Schema::dropIfExists('group_provider_list_procedure');
    }
}
