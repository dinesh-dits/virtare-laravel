<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BillingPercentageProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `billingPercentage`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `billingPercentage`(fromDate VARCHAR(50),toDate VARCHAR(50))
            BEGIN
            SELECT
                    ROUND(
                        (
                        SELECT
                            COUNT(isPaid)
                        FROM
                            patientTimeLogs
                        WHERE
                            isPaid = 0 AND patientTimeLogs.date >=fromDate  AND patientTimeLogs.date <= toDate AND patientTimeLogs.deletedAt IS NULL
                    ) * 100 /(COUNT(isPaid)),
                    2
                    ) AS `due`,
                    ROUND(
                        (
                        SELECT
                            COUNT(isPaid)
                        FROM
                            patientTimeLogs
                        WHERE
                            isPaid = 1 AND patientTimeLogs.date >= fromDate AND patientTimeLogs.date <= toDate AND patientTimeLogs.deletedAt IS NULL
                    ) * 100 /(COUNT(isPaid)),
                    2
                    ) AS `billed`
                FROM
                    patientTimeLogs
                WHERE
            patientTimeLogs.date >= fromDate AND patientTimeLogs.date <= toDate AND patientTimeLogs.deletedAt IS NULL;
    END
        ;";
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
