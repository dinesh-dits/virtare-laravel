<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EscalationCountProcedure extends Migration
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
        SELECT(COUNT(esculations.escalationType)) AS total,
        globalCodes.name AS text
        FROM
        esculations
        LEFT JOIN globalCodes
        ON globalCodes.id = esculations.escalationType
        GROUP BY
        esculations.escalationType ;
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
