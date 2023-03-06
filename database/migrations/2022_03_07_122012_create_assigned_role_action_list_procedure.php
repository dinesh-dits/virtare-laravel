<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAssignedRoleActionListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `assignedRolesActionList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `assignedRolesActionList`(IN staffId INT)
    BEGIN
    SELECT rolePermissions.actionId AS actionId,widgetAccesses.widgetId AS widgetId
        FROM userRoles 
        JOIN rolePermissions ON userRoles.accessRoleId = rolePermissions.id 
        JOIN widgetAccesses ON userRoles.accessRoleId =  widgetAccesses.id
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
        Schema::dropIfExists('assigned_role_action_list_procedure');
    }
}
