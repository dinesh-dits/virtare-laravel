<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRoleModuleScreensProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createRoleModuleScreen = "DROP PROCEDURE IF EXISTS `createRoleModuleScreen`;";

        DB::unprepared($createRoleModuleScreen);

        $createRoleModuleScreen = "
            CREATE PROCEDURE  createRoleModuleScreen
            (
                IN udid varchar(255), 
                IN providerId int,
                IN roleModuleId int, 
                IN screenId int, 
                IN description varchar(150),
                IN screenAccess TINYINT
            ) 
            BEGIN
            INSERT INTO roleModuleScreens 
            (
                udid,
                providerId,
                roleModuleId,
                screenId,
                description,
                screenAccess
            ) 
            values
            (
                udid,
                providerId,
                roleModuleId,
                screenId,
                description,
                screenAccess
            );
            END;";
  
        DB::unprepared($createRoleModuleScreen);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roleModuleScreens_procedure');
    }
}
