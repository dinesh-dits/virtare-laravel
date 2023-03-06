<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentTimelineProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalAppointmentSummaryCount`";
        DB::unprepared($procedure);

        
        $procedure = "CREATE PROCEDURE `getTotalAppointmentSummaryCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
            SELECT count(*) as total,
            appointments.startDateTime as duration,
            hour(appointments.startDateTime) as time
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
        Schema::dropIfExists('appointment_timeline_procedure');
    }
}
