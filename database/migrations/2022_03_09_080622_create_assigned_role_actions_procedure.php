<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAssignedRoleActionsProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `assignedRolesActionsList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `assignedRolesActionsList`(IN staffId INT)
    BEGIN
    SELECT  rolePermissions.actionId AS actionId
        FROM userRoles 
        
        JOIN rolePermissions ON userRoles.accessRoleId =  rolePermissions.accessRoleId
   WHERE
        userRoles.staffId = staffId AND userRoles.deletedAt IS NULL
        GROUP BY actionId;
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
        Schema::dropIfExists('assigned_role_actions_procedure');
    }
}
