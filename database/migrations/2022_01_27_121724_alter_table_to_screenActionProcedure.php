<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterTableToScreenActionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $screenAction = "DROP PROCEDURE IF EXISTS `createScreenAction`;
            CREATE PROCEDURE  createScreenAction(IN userId int, IN actionId int,IN deviceId int) 
            BEGIN
            INSERT INTO screenActions (userId,actionId,deviceId) values(userId,actionId,deviceId);
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
        Schema::table('screenActionProcedure', function (Blueprint $table) {
            //
        });
    }
}
