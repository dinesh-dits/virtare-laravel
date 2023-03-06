<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetVitalByMonthProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getVitalByMonth`;";
        DB::unprepared($procedure);
        $procedure = "CREATE PROCEDURE  getVitalByMonth(IN patientIdx INT,fromDate VARCHAR(50))
        BEGIN
        Select DAY(pv.createdAt) as currentDay,COUNT(pv.id) AS totalVitals,
        pv.id,pv.units,pv.patientId,pv.createdAt 
        FROM patientVitals as pv 
        WHERE 
        patientId = patientIdx AND pv.createdAt >= fromDate AND pv.createdAt <= DATE_ADD(pv.createdAt, INTERVAL 30 DAY) 
        GROUP BY DAY(pv.createdAt) 
        ORDER BY `pv`.`createdAt` ASC;
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
        // Schema::dropIfExists('getVitalByMonthProcedure');
    }
}
