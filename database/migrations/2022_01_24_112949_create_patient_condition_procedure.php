<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientConditionProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getPatientConditionsCount`;
        CREATE PROCEDURE `getPatientConditionsCount`()
        BEGIN
        SELECT
        COUNT(patientFlags.id) AS total,
           flags.name as text,
           flags.color as color,
           '#FFFFFF' as textColor,
           patientFlags.flagId as flagId
       FROM
           patientFlags
       JOIN flags ON patientFlags.flagId = flags.id
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
        Schema::dropIfExists('patient_condition_procedure');
    }
}
