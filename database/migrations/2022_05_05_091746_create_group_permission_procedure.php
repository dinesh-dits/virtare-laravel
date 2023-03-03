<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGroupPermissionProcedure extends Migration
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
          "CREATE PROCEDURE  createGroupPermission(IN udid varchar(255), IN groupId int,IN actionId int,IN createdBy int) 
            BEGIN
            INSERT INTO groupPermissions (udid,groupId,actionId,createdBy) values(udid,groupId,actionId,createdBy);
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
        Schema::dropIfExists('group_permission_procedure');
    }
}
