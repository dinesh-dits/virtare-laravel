<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AssignedRolesWidgetsGroupListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `assignedRolesWidgetsGroupList`';
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `assignedRolesWidgetsGroupList`(IN groupId INT,IN actionIdx VARCHAR(500))
    BEGIN
    SELECT  groupWidgets.widgetId AS id
        FROM groupWidgets
   WHERE
        groupWidgets.groupId = groupId AND (groupWidgets.actionId  IN (SELECT * FROM JSON_TABLE( actionIdx, "$[*]" COLUMNS( Value INT PATH "$" ) ) as s) 
		OR actionIdx="") AND groupWidgets.deletedAt IS NULL
        GROUP BY actionId;
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
