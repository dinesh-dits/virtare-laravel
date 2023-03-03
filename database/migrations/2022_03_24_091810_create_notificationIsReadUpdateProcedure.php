<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationIsReadUpdateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `notificationIsReadUpdate`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `notificationIsReadUpdate`(In Idx INT)
            BEGIN
            UPDATE notifications SET isRead = 1
            WHERE (notifications.userId=Idx)
            OR (notifications.id=Idx);
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
        Schema::dropIfExists('notification_is_read_update_procedure');
    }
}
