<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPatientFlagCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionsCount`;
        CREATE PROCEDURE `getPatientConditionsCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT(IF((patientFlags.createdAt IS NULL),
        0,
        COUNT(patientFlags.flagId)
            )
        ) AS total,
        flags.color as color,
        flags.name AS text,
        '#FFFFFF' as textColor,
        flags.id AS flagId
        FROM
        patientFlags
        RIGHT JOIN flags ON patientFlags.flagId = flags.id  
        WHERE
        (patientFlags.createdAt BETWEEN fromDate AND toDate) 
        GROUP BY
        flagId,text,color;
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