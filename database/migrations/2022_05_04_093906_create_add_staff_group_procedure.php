<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddStaffGroupProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `createStaffGroup`;";

        DB::unprepared($procedure);

        $procedure = 
          "CREATE PROCEDURE  createStaffGroup(IN udid varchar(255), IN providerId int,IN staffId  int,IN groupId int,IN createdBy int) 
            BEGIN
            INSERT INTO staffGroups (udid,providerId,staffId,groupId,createdBy) values(udid,providerId,staffId,groupId,createdBy);
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
        Schema::dropIfExists('add_staff_group_procedure');
    }
}
