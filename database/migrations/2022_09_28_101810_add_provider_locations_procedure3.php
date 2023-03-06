<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProviderLocationsProcedure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addProviderLocations`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `addProviderLocations`(IN data JSON)
        BEGIN
        INSERT INTO providerLocations 
        (udid,countryId,stateId,city,locationName,providerId,level,parent,isDefault,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.countryId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.stateId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.city")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.locationName")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.level")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.parent")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.isDefault")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT LAST_INSERT_ID() as id;
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
