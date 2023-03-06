<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGetVitalsProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getVitals`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `getVitals`(IN patientIdx INT,IN typeVital VARCHAR(50))
        BEGIN
        SELECT patientVitals.*, vitalFields.name vitalFieldName,patientFlags.icon AS icon,flags.name AS flagName,flags.color AS flagColor,patientFlags.icon AS icon,
        globalCodes.name AS deviceName
        FROM patientVitals 
        LEFT JOIN vitalFields 
        ON patientVitals.vitalFieldId=vitalFields.id 
        RIGHT JOIN vitalTypeFields 
        ON vitalFields.id=vitalTypeFields.vitalFieldId 
        LEFT JOIN patients
        ON `patientVitals`.patientId=patients.id 
        JOIN patientFlags
        ON patients.id=patientFlags.patientId
        LEFT JOIN flags
        ON patientFlags.flagId=flags.id
        LEFT JOIN globalCodes 
        ON vitalTypeFields.vitalTypeId=globalCodes.id 
        WHERE patientVitals.patientId = patientIdx
        AND(vitalFields.name=typeVital OR typeVital='')
        ORDER BY patientVitals.takeTime DESC;
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
        Schema::dropIfExists('get_vitals_procedure');
    }
}
