<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRolePermissionListingProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $procedure = "DROP PROCEDURE IF EXISTS `rolePermissionListing`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `rolePermissionListing`(IN idx INT)
    BEGIN
        SELECT *,
        (SELECT JSON_ARRAYAGG(JSON_ARRAY(JSON_OBJECT('name',screens.name, 'udid',screens.udid,'action',((SELECT JSON_ARRAYAGG(JSON_ARRAY(JSON_OBJECT('name',actions.name, 'udid',actions.udid)))
        FROM
            rolePermissions
        JOIN actions ON rolePermissions.actionId = actions.id
        JOIN screens as s ON actions.screenId = s.id
                                                                                                        
        WHERE
            rolePermissions.accessRoleId = idx and s.id = screens.id
    )))))
        FROM
            screens
        WHERE
            screens.moduleId = modules.id
        ) AS screens
 
        FROM
            modules
        WHERE
        modules.id IN(
        SELECT
            modules.id
        FROM
            `rolePermissions`
        JOIN actions ON rolePermissions.actionId = actions.id
        JOIN screens ON actions.screenId = screens.id
        JOIN modules ON screens.moduleId = modules.id
        WHERE
            `accessRoleId` = idx
        GROUP BY
            modules.id
);
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
