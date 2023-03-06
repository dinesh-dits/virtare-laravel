<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterStaffRoleProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createStaffRole = "DROP PROCEDURE IF EXISTS `createStaffRole`;";

        DB::unprepared($createStaffRole);

        $createStaffRole = 
       "CREATE PROCEDURE  createStaffRole(IN udid varchar(255), IN staffId int,IN accessRoleId int) 
        BEGIN
        INSERT INTO userRoles (udid,staffId,accessRoleId) values(udid,staffId,accessRoleId);
        END;";

    DB::unprepared($createStaffRole);
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
