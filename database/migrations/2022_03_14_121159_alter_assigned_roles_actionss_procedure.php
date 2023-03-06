<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterAssignedRolesActionssProcedure extends Migration
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
            "CREATE PROCEDURE `assignedRolesActionsList`(IN staffIdx INT)
    BEGIN
    SELECT  rolePermissions.actionId AS id
        FROM userRoles 
        
        JOIN rolePermissions ON userRoles.accessRoleId =  rolePermissions.accessRoleId
   WHERE
        userRoles.staffId = staffIdx AND userRoles.deletedAt IS NULL AND rolePermissions.deletedAt IS NULL
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
        //
    }
}
