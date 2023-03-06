<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProceduresDynamicStartEndDateForTimelineDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getGlobalStartEndDate`";
        DB::unprepared($procedure);

        $procedure = "CREATE PROCEDURE `getGlobalStartEndDate`(globalCodesId INT)
        BEGIN
        SELECT gse.udid, gse.globalCodeId, gse.conditions, gse.number as nm, gse.intervalType, CURDATE() as startDate,
        (
        CASE WHEN intervalType = 'Day' THEN
        DATE_ADD(CURDATE(),INTERVAL (SELECT CONCAT(conditions,number) FROM globalStartEndDate WHERE globalCodeId = globalCodesId) Day)
        WHEN intervalType = 'Month' THEN
        DATE_ADD(CURDATE(),INTERVAL (SELECT CONCAT(conditions,number) FROM globalStartEndDate WHERE globalCodeId = globalCodesId) Month)
        WHEN intervalType = 'Week' THEN
        DATE_ADD(CURDATE(),INTERVAL (SELECT CONCAT(conditions,number) FROM globalStartEndDate WHERE globalCodeId = globalCodesId) Week)
        ELSE
        DATE_ADD(CURDATE(),INTERVAL (SELECT CONCAT(conditions,number) FROM globalStartEndDate WHERE globalCodeId = globalCodesId) Year)
        END
        ) as endDate
        FROM globalStartEndDate as gse WHERE gse.globalCodeId = globalCodesId ORDER by globalCodes.priority ASC;;
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
        Schema::dropIfExists('procedures_dynamic_start_end_date_for_timeline_dates');
    }
}
