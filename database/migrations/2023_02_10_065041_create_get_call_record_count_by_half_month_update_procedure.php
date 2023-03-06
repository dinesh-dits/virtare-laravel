<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetCallRecordCountByHalfMonthUpdateProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getCallRecordCountByHalfMonth`;";
        DB::unprepared($procedure);
        $procedure = "CREATE PROCEDURE  getCallRecordCountByHalfMonth(patientIdx VARCHAR(250),fromDate VARCHAR(50))
        BEGIN
        SELECT communicationCallRecords.*,
        (sum(TIMESTAMPDIFF(SECOND,callRecordTimes.startTime,callRecordTimes.endTime))/60) as timeCall 
        FROM `communicationCallRecords` 
        inner join callRecords on callRecords.communicationCallRecordId = communicationCallRecords.id 
        inner join callRecordTimes on callRecordTimes.callRecordId = callRecords.id 
        WHERE `patientId` In (patientIdx) 
        AND communicationCallRecords.createdAt >= fromDate 
        AND communicationCallRecords.createdAt <= DATE_ADD(communicationCallRecords.createdAt, INTERVAL 15 DAY) 
        group by communicationCallRecords.patientId;
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
        Schema::dropIfExists('get_call_record_count_by_half_month_update_procedure');
    }
}
