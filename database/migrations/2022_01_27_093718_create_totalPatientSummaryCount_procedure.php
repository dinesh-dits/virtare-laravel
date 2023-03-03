<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalPatientSummaryCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getTotalPatientSummaryCount`;
        CREATE PROCEDURE `getTotalPatientSummaryCount`(timelineId INT(20))
        BEGIN
        IF timelineId = 122 THEN
            SELECT count(*) as total,
            DATE_FORMAT(patients.createdAt,'%h:%i %p') as time
            FROM patients
            WHERE createdAt > date_sub(now(), interval 1 day)
            GROUP BY (time);
        ELSEIF timelineId = 123 THEN
            SELECT count(*) as total,
            dayname(patients.createdAt) as week
            FROM patients
            WHERE createdAt > date_sub(now(), interval 7 day)
            GROUP BY (week);
        ELSEIF timelineID = 124 THEN
            SELECT count(*) as total,
            DATE_FORMAT(patients.createdAt,'%b %d,%Y') as day
            FROM patients
            WHERE createdAt > date_sub(now(), interval 1 month)
            GROUP BY (day);
        ELSEIF timelineId = 125 THEN
            SELECT count(*) as total,
            MONTHNAME(patients.createdAt) as month
            FROM patients
            WHERE createdAt > date_sub(now(), interval 1 year)
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
        Schema::dropIfExists('totalPatientSummaryCountProcedure');
    }
}
