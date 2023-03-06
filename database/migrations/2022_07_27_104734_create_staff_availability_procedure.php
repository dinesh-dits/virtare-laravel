<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffAvailabilityProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createStaffAvailability = "DROP PROCEDURE IF EXISTS `createStaffAvailability`;
            CREATE PROCEDURE  createStaffAvailability(IN providerId bigInt,IN udid varchar(255), IN startTime time,IN endTime time,  IN staffId int) 
            BEGIN
            INSERT INTO staffAvailabilities (providerId,udid,startTime,endTime,staffId) values(providerId,udid,startTime,endTime,staffId);
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
        Schema::dropIfExists('staff_availability_procedure');
    }
}
