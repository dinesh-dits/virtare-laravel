<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVitalFieldRangeFlagProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `vitalRangeFlag`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `vitalRangeFlag`(IN low INT,IN high INT)
    BEGIN
    SELECT vitalFields.name AS vitalFieldName , vitalFlags.name AS flagName,vitalFlags.color AS vitalColor
    FROM vitalFieldRanges
    JOIN vitalFields ON vitalFieldRanges.vitalFieldId = vitalFields.id
    JOIN vitalFlags ON vitalFieldRanges.vitalFlagId =  vitalFlags.id
    WHERE vitalFieldRanges.low = low AND vitalFieldRanges.high = high;
    
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
        Schema::dropIfExists('vital_field_range_flag_procedure');
    }
}
