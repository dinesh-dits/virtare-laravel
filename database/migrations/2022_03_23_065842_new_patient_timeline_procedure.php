<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewPatientTimelineProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getNewPatientCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `getNewPatientCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT
                COUNT(patients.id) AS total,
                'New' AS text,
                '#8E60FF' AS color,
                '#FFFFFF' AS textColor
                FROM patients
            WHERE
            patients.createdAt >= fromDate AND patients.createdAt <= toDate AND patients.deletedAt IS NULL;
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
