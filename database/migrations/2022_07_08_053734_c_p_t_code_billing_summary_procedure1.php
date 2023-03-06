<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CPTCodeBillingSummaryProcedure1 extends Migration
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
    (COUNT(cptCodeServices.cptCodeActivityId )* (cptCodes.billingAmout) )AS total,
    cptCodes.name as text
FROM
    `cptCodeServices`
JOIN cptCodeActivities ON cptCodeActivities.id = cptCodeServices.cptCodeActivityId
JOIN cptCodes ON cptCodeActivities.cptCodeId = cptCodes.id
WHERE cptCodeServices.createdAt >= fromDate AND cptCodeServices.createdAt <= toDate AND cptCodeServices.deletedAt IS NULL AND cptCodes.id != 6
GROUP BY
    cptCodeServices.cptCodeActivityId;
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
