<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddGroupProviderProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `createGroupProvider`;";

        DB::unprepared($procedure);

        $procedure = 
          "CREATE PROCEDURE  createGroupProvider(IN udid varchar(255), IN groupId int,IN providerId int,IN createdBy int) 
            BEGIN
            INSERT INTO groupProviders (udid,groupId,providerId,createdBy) values(udid,groupId,providerId,createdBy);
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
        Schema::dropIfExists('add_group_provider_procedure');
    }
}
