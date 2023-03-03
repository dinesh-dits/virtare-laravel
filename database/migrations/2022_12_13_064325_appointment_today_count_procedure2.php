<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppointmentTodayCountProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `appointmentTodayCount`';
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `appointmentTodayCount`(IN fromDate VARCHAR(50),IN toDate VARCHAR(50),IN idx INT,IN providerId INT,IN providerLocationId INT)
            BEGIN
            SELECT count(*) as total,
            appointments.startDateTime as duration,
            appointments.startDateTime as time
            FROM appointments
            WHERE appointments.startDateTime >= fromDate AND appointments.startDateTime <= toDate AND appointments.deletedAt IS NULL 
            AND (appointments.staffId=idx OR idx='')
            AND (appointments.providerId=providerId OR providerId='') AND (appointments.providerLocationId=providerLocationId OR providerLocationId='')
            group by appointments.id
            ORDER BY time;
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
        //
    }
}