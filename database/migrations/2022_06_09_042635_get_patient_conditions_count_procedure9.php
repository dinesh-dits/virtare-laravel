<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GetPatientConditionsCountProcedure9 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionsCount`;
        CREATE PROCEDURE `getPatientConditionsCount`(fromDate VARCHAR(50))
        BEGIN
        select *,count(flagId)as total from (SELECT flags.color as color,
        flags.name AS text, '#FFFFFF' as textColor, flags.id AS flagId 
        FROM patientFlags 
        LEFT JOIN flags 
        ON flags.id=patientFlags.flagId 
        LEFT JOIN globalCodes 
        ON globalCodes.id=flags.type 
        LEFT JOIN patients 
        ON patients.id= patientFlags.patientId 
        WHERE (globalCodes.name='Patient' OR globalCodes.name='Both') 
        AND patients.deletedAt IS NULL AND (patientFlags.createdAt >=fromDate OR patientFlags.deletedAt IS NULL) 
        GROUP BY concat(patients.id,'_',flagId)) as flagCount group by flagId;
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