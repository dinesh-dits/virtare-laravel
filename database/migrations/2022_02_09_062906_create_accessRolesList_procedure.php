<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessRolesListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `accessRolesList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `accessRolesList`()
    BEGIN
    SELECT accessRoles.*,
        accessRoles.roles AS role,
        accessRoles.roleDescription AS description,
        globalCodes.name AS roleType
        FROM accessRoles
        JOIN globalCodes ON accessRoles.roleTypeId = globalCodes.id;
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
        Schema::dropIfExists('accessRolesList_procedure');
    }
}
