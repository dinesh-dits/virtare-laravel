<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddPermissionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createPermission = "DROP PROCEDURE IF EXISTS `createPermission`;";

        DB::unprepared($createPermission);

        $createPermission = "
            CREATE PROCEDURE  createPermission(IN udid varchar(255), IN providerId int,IN providerLocationId int, 
            IN roleId int, IN actionId int) 
            BEGIN
            INSERT INTO permissions (udid,providerId,providerLocationId,roleId,actionId) 
            values(udid,providerId,providerLocationId,roleId,actionId);
            END;";
  
        DB::unprepared($createPermission);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addPermission_procedure');
    }
}
