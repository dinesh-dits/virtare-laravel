<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCreatestaffRoleProcedureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createStaffRole = "DROP PROCEDURE IF EXISTS `createStaffRole`;
            CREATE PROCEDURE  createStaffRole(IN udid varchar(255), IN staffId int,IN roleId int) 
            BEGIN
            INSERT INTO userRoles (udid,staffId,roleId) values(udid,staffId,roleId);
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
        Schema::dropIfExists('createstaffRoleProcedure');
    }
}
