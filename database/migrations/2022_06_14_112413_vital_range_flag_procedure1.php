<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VitalRangeFlagProcedure1 extends Migration
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
            "CREATE PROCEDURE `vitalRangeFlag`(IN vitalFieldIdx INT,IN valueNumber INT)
    BEGIN
    SELECT vitalFields.name AS vitalFieldName , vitalFlags.name AS flagName,vitalFlags.color AS vitalColor, vitalFlags.id AS vitalFlagId
    FROM vitalFieldRanges
    RIGHT JOIN vitalFields ON vitalFieldRanges.vitalFieldId = vitalFields.id
   RIGHT JOIN vitalFlags ON vitalFieldRanges.vitalFlagId =  vitalFlags.id
    WHERE vitalFields.id = vitalFieldIdx AND (valueNumber BETWEEN vitalFieldRanges.low AND vitalFieldRanges.high);
    
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
