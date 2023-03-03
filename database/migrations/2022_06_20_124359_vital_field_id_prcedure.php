<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VitalFieldIdPrcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `vitalFieldId`;";
        DB::unprepared($procedure);
        $procedure = "CREATE PROCEDURE vitalFieldId(IN idx INT, IN typeIdx INT) 
        BEGIN
        SELECT patientVitals.id 
        FROM patientVitals 
        LEFT JOIN vitalFields
        ON vitalFields.id = patientVitals.vitalfieldid
        LEFT JOIN vitalTypeFields
        ON vitalTypeFields.vitalFieldId = vitalFields.id
        WHERE patientVitals.taketime IN ( SELECT max(patientVitals.taketime) 
        FROM patientVitals WHERE (patientVitals.vitalfieldid =idx OR idx='') AND (vitalTypeFields.vitalTypeId=typeIdx OR typeIdx='') GROUP BY patientVitals.patientid) 
        AND (patientVitals.vitalFieldId =idx OR idx='') AND (vitalTypeFields.vitalTypeId=typeIdx OR typeIdx='');
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
