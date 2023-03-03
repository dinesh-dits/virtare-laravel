<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class NotificationIsReadListProcedure extends Migration
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
            "CREATE PROCEDURE `notificationIsReadList`()
            BEGIN
            SELECT
            notifications.body AS body,
            notifications.title AS title,
            notifications.referenceId AS referenceId,
            notifications.entity AS entity,
            notifications.createdAt AS createdAt, 
            notifications.isRead AS isRead
            FROM
            notifications
            WHERE notifications.isRead=0;
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
