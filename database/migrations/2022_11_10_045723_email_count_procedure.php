<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class EmailCountProcedure extends Migration
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
        CREATE PROCEDURE  emailCount()
        BEGIN
        SELECT COUNT(email_stats.status) AS total,
        email_stats.status AS status
        FROM
        email_stats
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
