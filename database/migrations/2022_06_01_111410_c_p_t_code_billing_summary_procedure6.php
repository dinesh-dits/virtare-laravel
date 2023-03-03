<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CPTCodeBillingSummaryProcedure6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `CPTCodeBillingSummary`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `CPTCodeBillingSummary`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT
    (COUNT(patientTimeLogs.cptCodeId )* (cptCodes.billingAmout) )AS total,
    cptCodes.name as text
FROM
    `patientTimeLogs`
JOIN cptCodes ON patientTimeLogs.cptCodeId = cptCodes.id
WHERE patientTimeLogs.date >= fromDate AND patientTimeLogs.date <= toDate AND patientTimeLogs.deletedAt IS NULL AND cptCodes.id != 6
GROUP BY
    patientTimeLogs.cptCodeId;
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
