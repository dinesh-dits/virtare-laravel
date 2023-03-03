<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardAppointment1YearProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `dashboardYearAppointment`';
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `dashboardYearAppointment`(IN fromDate VARCHAR(50),IN toDate VARCHAR(50))
            BEGIN
            SELECT count(*) as total,
            appointments.startDateTime as duration,
            MONTHNAME(appointments.startDateTime) as time
            FROM appointments
            WHERE appointments.startDateTime >= fromDate AND appointments.startDateTime <= toDate AND appointments.deletedAt IS NULL
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
        Schema::dropIfExists('dashboard_appointment1_year_procedure');
    }
}
