<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolePermissionProcedure extends Migration
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
            "CREATE PROCEDURE  createRolePermission(IN providerId bigInt,IN udid varchar(255), IN accessRoleId int,IN actionId int) 
            BEGIN
            INSERT INTO rolePermissions (providerId,udid,accessRoleId,actionId) values(providerId,udid,accessRoleId,actionId);
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
        Schema::dropIfExists('role_permission_procedure');
    }
}
