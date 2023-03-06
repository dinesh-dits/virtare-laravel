<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EscalationWeeksCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `escalationWeekCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  escalationWeekCount(IN idx int,IN fromDate VARCHAR(50),IN toDate VARCHAR(50)) 
        BEGIN
        SELECT(COUNT(escalationTypes.escalationTypesId)) AS total,
        globalCodes.name AS text,
        globalCodes.id AS id,
        dayname(escalations.dueBy) as time
        FROM
        escalationTypes
        LEFT JOIN escalations
        ON escalations.escalationId = escalationTypes.escalationId
        LEFT JOIN globalCodes
        ON globalCodes.id = escalationTypes.escalationTypeId
        LEFT JOIN escalationStaff
        ON escalationStaff.escalationId = escalations.escalationId
        WHERE (escalationStaff.staffId=idx OR idx='')
        AND escalations.dueBy >= fromDate AND escalations.dueBy <= toDate AND escalations.deletedAt IS NULL AND escalationTypes.deletedAt IS NULL
        GROUP BY
        escalationTypes.escalationTypeId ;
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
