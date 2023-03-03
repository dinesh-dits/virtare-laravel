<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleSearchProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $search = "DROP PROCEDURE IF EXISTS `roleSearch`;";
        DB::unprepared($search);
        $search = "
            CREATE PROCEDURE  roleSearch(IN search VARCHAR(100))
            BEGIN
            SELECT
                accessRoles.Id AS id,
                accessRoles.udid AS udid,
                accessRoles.roles AS roles,
                accessRoles.roleDescription AS description,
                accessRoles.roleTypeId AS roleTypeId,
                globalCodes.name as roleType
                FROM
                accessRoles
                LEFT JOIN globalCodes ON accessRoles.roleTypeId = globalCodes.id
                WHERE
                accessRoles.roles LIKE CONCAT('%', search , '%');
            END;";
        DB::unprepared($search);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_search_procedure');
    }
}
