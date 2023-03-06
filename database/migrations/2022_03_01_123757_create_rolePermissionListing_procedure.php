<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolePermissionListingProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `rolePermissionListing`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `rolePermissionListing`(IN idx INT)
    BEGIN
    SELECT
            rolePermissions.actionId AS actionId,rolePermissions.accessRoleId,
            actions.name,
            actions.controller,
            actions.function
    FROM
           rolePermissions
    JOIN actions ON actions.id = rolePermissions.actionId
    WHERE
          rolePermissions.accessRoleId = idx
    GROUP BY
    rolePermissions.accessRoleId,
    rolePermissions.actionId;
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
        Schema::dropIfExists('role_permission_listing_procedure');
    }
}
