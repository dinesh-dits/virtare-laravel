<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterNotificationIsReadListssProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `notificationIsReadList`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `notificationIsReadList`(IN idx int)
            BEGIN
            SELECT
            notifications.id AS id,
            notifications.body AS body,
            notifications.title AS title,
            notifications.referenceId AS referenceId,
            notifications.entity AS entity,
            notifications.createdAt AS createdAt, 
            notifications.isRead AS isRead
            FROM
            notifications
            WHERE notifications.userId=idx AND notifications.isRead=0 ORDER BY notifications.id DESC;
            END    
        ;";
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
