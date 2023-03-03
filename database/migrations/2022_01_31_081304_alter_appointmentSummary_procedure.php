<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAppointmentSummaryProcedure extends Migration
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
            GROUP BY (time)
            ORDER BY appointments.startTime;
        ELSEIF timelineId = 123 THEN
            SELECT count(*) as total,
            dayname(appointments.startDate) as week
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 week)
            GROUP BY week
            ORDER BY appointments.startDate;
        ELSEIF timelineID = 124 THEN
            SELECT count(*) as total,
            DATE_FORMAT(appointments.startDate,'%b %d,%Y') as day
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 month)
            GROUP BY (day)
            ORDER BY appointments.startDate;
        ELSEIF timelineId = 125 THEN
            SELECT count(*) as total,
            MONTHNAME(appointments.startDate) as month
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 year)
            GROUP BY (month)
            ORDER BY appointments.startDate;
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
