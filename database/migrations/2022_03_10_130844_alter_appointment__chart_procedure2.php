<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAppointmentChartProcedure2 extends Migration
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
        appointments.startDateTime as duration,
        hour(appointments.startDateTime) as time
        FROM appointments
        WHERE startDateTime > concat(curdate(),' 00:00:00') AND startDateTime < concat(curdate(),' 23:59:59')
        GROUP BY time;
        ELSEIF timelineId = 123 THEN
            SELECT count(*) as total,
            dayname(appointments.startDateTime) as duration
            FROM appointments
            WHERE startDateTime > date_sub(curdate(), interval 7 day)
            GROUP BY (duration);
        ELSEIF timelineID = 124 THEN
            SELECT count(*) as total,
            DATE_FORMAT(appointments.startDateTime,'%b %d,%Y') as duration
            FROM appointments
            WHERE startDateTime > date_sub(curdate(), interval 1 month)
            GROUP BY (duration);
        ELSEIF timelineId = 125 THEN
            SELECT count(*) as total,
            MONTHNAME(appointments.startDateTime) as duration
            FROM appointments
            WHERE startDateTime > date_sub(curdate(), interval 1 year)
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
