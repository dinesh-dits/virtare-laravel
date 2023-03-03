<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CommunicationStatusCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `communicationStatusCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `communicationStatusCount`(fromDate VARCHAR(50),toDate VARCHAR(50),IN providerId INT, IN providerLocationId INT)
        BEGIN
        SELECT
        (IF((communicationCallRecords.createdAt IS NULL),
            0,
            COUNT(communicationCallRecords.callStatusId)
        )
    ) AS total,
    globalCodes.name AS text
                FROM communicationCallRecords
                RIGHT JOIN globalCodes ON communicationCallRecords.callStatusId = globalCodes.id
            WHERE
            globalCodes.globalCodeCategoryId = 41 AND communicationCallRecords.createdAt >= fromDate AND communicationCallRecords.createdAt <= toDate AND communicationCallRecords.deletedAt IS NULL
            AND (communicationCallRecords.providerId = providerId OR providerId='') AND (communicationCallRecords.providerLocationId = providerLocationId OR providerLocationId='')
            GROUP BY
    globalCodes.id;
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
