<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatestaffRoleProcedure extends Migration
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
            "CREATE PROCEDURE  createStaffRole(IN providerId bigInt,IN udid varchar(255), IN staffId int,IN accessRoleId int) 
        BEGIN
        INSERT INTO userRoles (providerId,udid,staffId,accessRoleId) values(providerId,udid,staffId,accessRoleId);
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
