<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPermissionProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `createGroupPermission`;";

        DB::unprepared($procedure);

        $procedure = 
          "CREATE PROCEDURE  createGroupPermission(IN udid varchar(255),IN providerId INT,IN providerLocationId INT, IN groupId int,IN actionId int,IN createdBy int) 
            BEGIN
            INSERT INTO groupPermissions (udid,providerId,providerLocationId,groupId,actionId,createdBy) values(udid,providerId,providerLocationId,groupId,actionId,createdBy);
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
        Schema::dropIfExists('group_permission_procedure1');
    }
}
