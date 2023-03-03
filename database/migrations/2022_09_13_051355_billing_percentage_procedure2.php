<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BillingPercentageProcedure2 extends Migration
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
            "CREATE PROCEDURE `billingPercentage`(fromDate VARCHAR(50),toDate VARCHAR(50),IN providerId INT,IN providerLocationId INT)
            BEGIN
            SELECT
                    ROUND(
                        (
                        SELECT
                            COUNT(status)
                        FROM
                            cptCodeServices
                        WHERE
                            status = 297 AND cptCodeServices.createdAt >=fromDate  AND cptCodeServices.createdAt <= toDate AND cptCodeServices.deletedAt IS NULL
                    ) * 100 /(COUNT(status)),
                    2
                    ) AS `logged`,
                    ROUND(
                        (
                        SELECT
                            COUNT(status)
                        FROM
                            cptCodeServices
                        WHERE
                            status = 298 AND cptCodeServices.createdAt >= fromDate AND cptCodeServices.createdAt <= toDate AND cptCodeServices.deletedAt IS NULL
                    ) * 100 /(COUNT(status)),
                    2
                    ) AS `billed`,
					ROUND(
                        (
                        SELECT
                            COUNT(status)
                        FROM
                            cptCodeServices
                        WHERE
                            status = 299 AND cptCodeServices.createdAt >= fromDate AND cptCodeServices.createdAt <= toDate AND cptCodeServices.deletedAt IS NULL
                    ) * 100 /(COUNT(status)),
                    2
                    ) AS `paid`,
					ROUND(
                        (
                        SELECT
                            COUNT(status)
                        FROM
                            cptCodeServices
                        WHERE
                            status = 300 AND cptCodeServices.createdAt >= fromDate AND cptCodeServices.createdAt <= toDate AND cptCodeServices.deletedAt IS NULL
                    ) * 100 /(COUNT(status)),
                    2
                    ) AS `unPaid`
                FROM
                    cptCodeServices
                WHERE
            cptCodeServices.createdAt >= fromDate AND cptCodeServices.createdAt <= toDate AND cptCodeServices.deletedAt IS NULL
            AND (cptCodeServices.providerId = providerId OR providerId='') AND (cptCodeServices.providerLocationId = providerLocationId OR providerLocationId='');
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
