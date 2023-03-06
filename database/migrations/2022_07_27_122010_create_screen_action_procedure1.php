<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScreenActionProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $screenAction = "DROP PROCEDURE IF EXISTS `createScreenAction`;
            CREATE PROCEDURE  createScreenAction(IN providerId int,IN userId int, IN actionId int,IN deviceId int) 
            BEGIN
            INSERT INTO screenActions (providerId,userId,actionId,deviceId) values(providerId,userId,actionId,deviceId);
            END;";
        DB::unprepared($screenAction);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('screen_action_procedure1');
    }
}
