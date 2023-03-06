<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunicationStatusCountProcedure extends Migration
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
            "CREATE PROCEDURE `communicationStatusCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
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
        Schema::dropIfExists('communication_status_count_procedure');
    }
}
