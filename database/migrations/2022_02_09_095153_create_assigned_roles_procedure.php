<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssignedRolesProcedure extends Migration
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
        JOIN accessRoles ON accessRoles.roleId = accessRoles.id
        WHERE userRoles.staffId = staffId;
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
        Schema::dropIfExists('assigned_roles_procedure');
    }
}
