<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EmailCountProcedure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `emailCount`;";
        DB::unprepared($procedure);
        $procedure = "
        CREATE PROCEDURE emailCount(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT COUNT(email_stats.status) AS total,
        email_stats.status AS status
        FROM
        email_stats
        WHERE (email_stats.sent_on >=fromDate OR fromDate='') AND (email_stats.sent_on <=toDate OR toDate='')
        GROUP BY email_stats.status;
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
