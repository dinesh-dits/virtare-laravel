<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAssignedRolesWidgetsListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `assignedRolesWidgetsList`';
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `assignedRolesWidgetsList`(IN staffIdx INT,IN actionIdx VARCHAR(500))
    BEGIN
    SELECT  widgetAccesses.widgetId AS id
        FROM userRoles
        RIGHT JOIN widgetAccesses ON userRoles.accessRoleId =  widgetAccesses.accessRoleId
   WHERE
        userRoles.staffId = staffIdx AND (widgetAccesses.widgetId  IN (SELECT * FROM JSON_TABLE( actionIdx, "$[*]" COLUMNS( Value INT PATH "$" ) ) as s) OR actionIdx="") AND userRoles.deletedAt IS NULL AND widgetAccesses.deletedAt IS NULL
        GROUP BY widgetId;
    END;';
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
