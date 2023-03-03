<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AssignedRolesActionsGroupListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `assignedRolesActionsGroupList`';
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `assignedRolesActionsGroupList`(IN groupId INT,IN actionIdx VARCHAR(500))
    BEGIN
    SELECT  groupPermissions.actionId AS id
        FROM groupPermissions
   WHERE
        groupPermissions.groupId = groupId AND (groupPermissions.actionId  IN (SELECT * FROM JSON_TABLE( actionIdx, "$[*]" COLUMNS( Value INT PATH "$" ) ) as s) OR actionIdx="") AND groupPermissions.deletedAt IS NULL
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
