<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAssignedRolesListing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `assignedRolesList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `assignedRolesList`(IN staffId INT)
    BEGIN
    SELECT userRoles.*,
        accessRoles.roles AS role,
        staffs.firstName AS StaffName
        FROM userRoles
        JOIN staffs ON userRoles.staffId = staffs.id
        JOIN accessRoles ON userRoles.accessRoleId = accessRoles.id
        WHERE userRoles.staffId = staffId AND userRoles.deletedAt IS NULL;
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
