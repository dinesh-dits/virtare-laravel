<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStaffAvailabilityProcedureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createStaffAvailability = "DROP PROCEDURE IF EXISTS `createStaffAvailability`;
            CREATE PROCEDURE  createStaffAvailability(IN udid varchar(255), IN startTime time,IN endTime time,  IN staffId int) 
            BEGIN
            INSERT INTO staffAvailabilities (udid,startTime,endTime,staffId) values(udid,startTime,endTime,staffId);
            END;";
  
        DB::unprepared($createStaffAvailability);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staffAvailabilityProcedure');
    }
}
