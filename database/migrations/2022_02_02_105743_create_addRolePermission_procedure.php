<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddRolePermissionProcedure extends Migration
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

        $createRolePermission = "
            CREATE PROCEDURE  createRolePermission(IN udid varchar(255), IN providerId int,IN permissionId int, 
            IN roleModuleScreenId int, IN actionId int, IN actionAccess tinyint(1)) 
            BEGIN
            INSERT INTO rolePermissions (udid,providerId,permissionId,roleModuleScreenId,actionId,actionAccess) 
            values(udid,providerId,permissionId,roleModuleScreenId,actionId,actionAccess);
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
        Schema::dropIfExists('addRolePermission_procedure');
    }
}
