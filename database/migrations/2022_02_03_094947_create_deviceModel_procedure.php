<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceModelProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `deviceModelList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `deviceModelList`(IN deviceType INT)
    BEGIN
    IF deviceType = '' THEN
            SELECT 
            globalCodes.name AS deviceType,
            deviceModels.modelName AS modelNumber
            FROM deviceModels 
            INNER JOIN globalCodes ON globalCodes.id = deviceModels.deviceTypeId;
    ELSE
            SELECT
            globalCodes.name AS deviceType,
            deviceModels.modelName AS modelNumber
            FROM deviceModels 
            INNER JOIN globalCodes ON globalCodes.id = deviceModels.deviceTypeId
            WHERE deviceModels.deviceTypeId = deviceType;
    END IF;
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
        Schema::dropIfExists('device_model_procedure');
    }
}
