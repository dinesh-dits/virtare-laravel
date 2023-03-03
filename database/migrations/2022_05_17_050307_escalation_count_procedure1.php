<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EscalationCountProcedure1 extends Migration
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
        SELECT(COUNT(escalations.escalationType)) AS total,
        globalCodes.name AS text
        FROM
        escalations
        LEFT JOIN globalCodes
        ON globalCodes.id = escalations.escalationType
        GROUP BY
        escalations.escalationType ;
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
