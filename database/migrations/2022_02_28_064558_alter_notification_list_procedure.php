<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNotificationListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       $procedure = "DROP PROCEDURE IF EXISTS `notificationList`;";
        DB::unprepared($procedure);
        $procedure = "CREATE PROCEDURE  notificationList(isSend int,reciverId int) 
        BEGIN
        SELECT  *
                FROM    notifications 
                WHERE   `isSent` = isSend AND (`userId` = reciverId OR reciverId='')
                ORDER BY createdAt DESC;
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
       $procedure = "DROP PROCEDURE IF EXISTS `notificationList`;";
        DB::unprepared($procedure);
    }
}
