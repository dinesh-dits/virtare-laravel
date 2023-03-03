<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EscalationCountProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `escalationCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE  escalationCount() 
        BEGIN
        SELECT(COUNT(escalationTypes.escalationTypesId)) AS total,
        globalCodes.name AS text,
        globalCodes.id AS escalationTypesId
        FROM
        escalationTypes
        LEFT JOIN escalations
        ON escalations.escalationId = escalationTypes.escalationId
        LEFT JOIN globalCodes
        ON globalCodes.id = escalationTypes.escalationTypeId
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
