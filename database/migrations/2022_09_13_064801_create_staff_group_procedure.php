<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffGroupProcedure extends Migration
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
          "CREATE PROCEDURE  createStaffGroup(IN udid varchar(255), IN providerId int, IN providerLocationId int,IN staffId  int,IN groupId int,IN createdBy int) 
            BEGIN
            INSERT INTO staffGroups (udid,providerId,providerLocationId,staffId,groupId,createdBy) values(udid,providerId,providerLocationId,staffId,groupId,createdBy);
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
        Schema::dropIfExists('staff_group_procedure');
    }
}
