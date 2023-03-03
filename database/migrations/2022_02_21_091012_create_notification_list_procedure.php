<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `notificationList`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `notificationList`()
        BEGIN
            SELECT * 
            FROM `appointments`
            WHERE startDateTime >= NOW()  AND startDateTime <= NOW() + INTERVAL 1 HOUR;
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
        Schema::dropIfExists('notification_list_procedure');
    }
}
