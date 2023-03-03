<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterAssignedRolesWidgetssProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `assignedRolesWidgetsList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `assignedRolesWidgetsList`(IN staffIdx INT)
    BEGIN
    SELECT  widgetAccesses.widgetId AS id
        FROM userRoles 
        
        JOIN widgetAccesses ON userRoles.accessRoleId =  widgetAccesses.accessRoleId
   WHERE
        userRoles.staffId = staffIdx AND userRoles.deletedAt IS NULL AND widgetAccesses.deletedAt IS NULL
        GROUP BY widgetId;
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
