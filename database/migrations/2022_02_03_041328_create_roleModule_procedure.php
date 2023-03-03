<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRoleModuleProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createRoleModule = "DROP PROCEDURE IF EXISTS `createRoleModule`;";

        DB::unprepared($createRoleModule);

        $createRoleModule = "
            CREATE PROCEDURE  createRoleModule
            (
                IN udid varchar(255), 
                IN providerId int,
                IN roleId int, 
                IN moduleId int, 
                IN moduleAccess TINYINT
            ) 
            BEGIN
            INSERT INTO roleModules 
            (
                udid,
                providerId,
                roleId,
                moduleId,
                moduleAccess
            ) 
            values
            (
                udid,
                providerId,
                roleId,
                moduleId,
                moduleAccess
            );
            END;";
  
        DB::unprepared($createRoleModule);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roleModule_procedure');
    }
}
