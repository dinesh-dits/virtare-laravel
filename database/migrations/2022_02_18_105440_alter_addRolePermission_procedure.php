<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterAddRolePermissionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createRolePermission = "DROP PROCEDURE IF EXISTS `createRolePermission`;";

        DB::unprepared($createRolePermission);

        $createRolePermission = 
          "CREATE PROCEDURE  createRolePermission(IN udid varchar(255), IN accessRoleId int,IN actionId int) 
            BEGIN
            INSERT INTO rolePermissions (udid,accessRoleId,actionId) values(udid,accessRoleId,actionId);
            END;";
  
        DB::unprepared($createRolePermission);
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
