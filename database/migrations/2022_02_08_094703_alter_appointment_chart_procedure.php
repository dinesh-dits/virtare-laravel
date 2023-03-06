<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAppointmentChartProcedure extends Migration
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
            UNIX_TIMESTAMP(appointments.startTime) as duration,
            hour(appointments.startTime) as time
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 day)
            GROUP BY (time);
        ELSEIF timelineId = 123 THEN
            SELECT count(*) as total,
            dayname(appointments.startDate) as duration
            FROM appointments
            WHERE startDate > date_sub(now(), interval 7 day)
            GROUP BY (duration);
        ELSEIF timelineID = 124 THEN
            SELECT count(*) as total,
            DATE_FORMAT(appointments.startDate,'%b %d,%Y') as duration
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 month)
            GROUP BY (duration);
        ELSEIF timelineId = 125 THEN
            SELECT count(*) as total,
            MONTHNAME(appointments.startDate) as duration
            FROM appointments
            WHERE startDate > date_sub(now(), interval 1 year)
            GROUP BY (duration);
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
        //
    }
}
