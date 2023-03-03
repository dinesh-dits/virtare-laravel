<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CPTCodeBillingSummaryProcedure extends Migration
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
WHERE patientTimeLogs.createdAt >= fromDate AND patientTimeLogs.createdAt <= toDate AND patientTimeLogs.deletedAt IS NULL
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
        Schema::dropIfExists('CPTCodeBillingSummary_procedure');
    }
}
