<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProcedureListNotification extends Migration
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
        $procedure = "CREATE PROCEDURE  notificationList(isSent int,userId int) 
        BEGIN
        SELECT  *
                FROM    notifications 
                WHERE   `isSent` = isSent AND (`userId` = userId OR userId='')
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
        //
    }
}
