<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MergeWidgetsProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `mergeWidgets`';
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `mergeWidgets`(IN staffIdx INT,IN groupId INT,IN actionIdx VARCHAR(500))
        BEGIN
        SELECT  widgetAccesses.widgetId AS id
        FROM userRoles
        RIGHT JOIN widgetAccesses ON userRoles.accessRoleId =  widgetAccesses.accessRoleId
   WHERE
        userRoles.staffId = staffIdx AND (widgetAccesses.widgetId  IN (SELECT * FROM JSON_TABLE( actionIdx, "$[*]" COLUMNS( Value INT PATH "$" ) ) as s) 
		OR actionIdx="") AND userRoles.deletedAt IS NULL AND widgetAccesses.deletedAt IS NULL
        UNION
        SELECT  groupWidgets.widgetId AS id
        FROM groupWidgets
   WHERE
        groupWidgets.groupId = groupId AND (groupWidgets.widgetId  IN (SELECT * FROM JSON_TABLE( actionIdx, "$[*]" COLUMNS( Value INT PATH "$" ) ) as s) 
		OR actionIdx="") AND groupWidgets.deletedAt IS NULL;
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
