<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateAddProvidersProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addProvider`";
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `addProvider`(IN data TEXT)
        BEGIN
        INSERT INTO providers 
        (udid,name,address,countryId,stateId,city,zipcode,phoneNumber,tagId,moduleId,isActive,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.name")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.address")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.countryId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.stateId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.city")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.zipcode")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.phoneNumber")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.tagId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.moduleId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.isActive")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
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
        Schema::dropIfExists('addProviders_procedure');
    }
}
