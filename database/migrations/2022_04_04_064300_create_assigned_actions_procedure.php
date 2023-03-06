<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignedActionsProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `assignedRolesActionsList`';
        DB::unprepared($procedure);

        $procedure =
            'CREATE PROCEDURE `assignedRolesActionsList`(IN staffIdx INT,IN actionIdx VARCHAR(500))
    BEGIN
    SELECT  rolePermissions.actionId AS id
        FROM userRoles
        RIGHT JOIN rolePermissions ON userRoles.accessRoleId =  rolePermissions.accessRoleId
   WHERE
        userRoles.staffId = staffIdx AND (rolePermissions.actionId  IN (SELECT * FROM JSON_TABLE( actionIdx, "$[*]" COLUMNS( Value INT PATH "$" ) ) as s) OR actionIdx="") AND userRoles.deletedAt IS NULL AND rolePermissions.deletedAt IS NULL
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
        Schema::dropIfExists('assigned_actions_procedure');
    }
}
