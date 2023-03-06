<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalAppointmentSummaryCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalAppointmentSummaryCount`;
        CREATE PROCEDURE `getTotalAppointmentSummaryCount`(timelineId INT(20))
        BEGIN
        IF timelineId = 122 THEN
             SELECT count(*) as total,
            DATE_FORMAT(appointments.startTime,'%h:%i %p') as time
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 day)
            GROUP BY (time);
        ELSEIF timelineId = 123 THEN
            SELECT count(*) as total,
            dayname(appointments.startDate) as week
            FROM appointments
            WHERE startDate > date_sub(now(), interval 7 day)
            GROUP BY (week);
        ELSEIF timelineID = 124 THEN
            SELECT count(*) as total,
            DATE_FORMAT(appointments.startDate,'%b %d,%Y') as day
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 month)
            GROUP BY (day);
        ELSEIF timelineId = 125 THEN
            SELECT count(*) as total,
            MONTHNAME(appointments.startDate) as month
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 year)
            GROUP BY (month);
        END IF;
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
        Schema::dropIfExists('totalAppointmentSummaryCountProcedure');
    }
}
