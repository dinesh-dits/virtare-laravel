<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddProviderLocationsProcedure extends Migration
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
        (udid,providerId,locationName,numberOfLocations,locationAddress,stateId,city,zipCode,phoneNumber,email ,websiteUrl,isDefault,isActive,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.providerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.locationName")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.numberOfLocations")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.locationAddress")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.stateId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.city")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.zipCode")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.phoneNumber")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.email")) ,JSON_UNQUOTE(JSON_EXTRACT(data, "$.websiteUrl")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.isDefault")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.isActive")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
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
        Schema::dropIfExists('addProviderLocations_procedure');
    }
}
