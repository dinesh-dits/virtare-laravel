<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetGlobalStartEndDateProcedure extends Migration
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
        $procedure = "CREATE PROCEDURE `getGlobalStartEndDate`(In globalCodesId INT)
        BEGIN
        SELECT gse.udid, gse.globalCodeId, gse.conditions, gse.number as nm, globalCodes.name AS globalCodeName,gse.intervalType
        FROM globalStartEndDate as gse 
		JOIN globalCodes 
		ON globalCodes.id = gse.globalCodeId
		WHERE gse.globalCodeId = globalCodesId OR globalCodesId = '' ORDER by globalCodes.priority ASC;;
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
