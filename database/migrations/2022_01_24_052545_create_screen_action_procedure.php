<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateScreenActionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $screenAction = "DROP PROCEDURE IF EXISTS `createScreenAction`;";
  
        DB::unprepared($screenAction);

        $screenAction = "CREATE PROCEDURE createScreenAction(IN userId int, IN actionId int,IN deviceId int)
        BEGIN

            INSERT INTO `screen_actions`(`userId`, `actionId`, `deviceId`) VALUES (userId,actionId,deviceId);


            
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
        Schema::dropIfExists('screen_action_procedure');
    }
}
