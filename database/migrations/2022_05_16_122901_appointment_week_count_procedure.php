<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppointmentWeekCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `appointmentWeekCount`';
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `appointmentWeekCount`(IN fromDate VARCHAR(50),IN toDate VARCHAR(50),IN idx INT)
            BEGIN
            SELECT count(*) as total,
            appointments.startDateTime as duration,
            dayname(appointments.startDateTime) as time
            FROM appointments
            WHERE appointments.startDateTime >= fromDate AND appointments.startDateTime <= toDate AND appointments.deletedAt IS NULL
            AND (appointments.staffId=idx OR idx='')
            GROUP BY time;
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
