<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupPermissionListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `groupPermissionList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `groupPermissionList`(IN Idx INT,IN providerId INT, IN providerLocationId INT)
    BEGIN
    SELECT
    *,
    (
    SELECT
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'id',
                screens.id,
                'name',
                screens.name,
                'udid',
                screens.udid,
                'actions',
                (
                    (
                    SELECT
                        JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'id',
                                actions.id,
                                'udid',
                                actions.udid,
                                'name',
                                actions.name,
                                'controller',
                                actions.controller,
                                'function',
                                actions.function
                            )
                        )
                    FROM
                        groupPermissions
                    JOIN actions ON groupPermissions.actionId = actions.id
                    JOIN screens AS s
                    ON
                        actions.screenId = s.id
                    WHERE
                        groupPermissions.groupId = Idx AND s.id = screens.id AND groupPermissions.deletedAt IS NULL
                        AND (groupPermissions.providerId = providerId OR providerId='') AND (groupPermissions.providerLocationId = providerLocationId OR providerLocationId='')
                )
                )
            )
        )
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
        `groupPermissions`
    JOIN actions ON groupPermissions.actionId = actions.id
    JOIN screens ON actions.screenId = screens.id
    JOIN modules ON screens.moduleId = modules.id
    WHERE
        `groupId` = Idx AND groupPermissions.deletedAt IS NULL
        AND (groupPermissions.providerId = providerId OR providerId='') AND (groupPermissions.providerLocationId = providerLocationId OR providerLocationId='')
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
