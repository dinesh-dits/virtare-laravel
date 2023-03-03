<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcedureOfInventoryList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `inventoryList`";
        DB::unprepared($procedure);

        $procedure =
            "CREATE PROCEDURE `inventoryList`(IN isAvailable TINYINT,IN deviceType INT)
        BEGIN
        SELECT
            inventories.*,
         inventories.udid AS udid,
         deviceModels.modelName AS modelNumber,
         globalCodes.name AS deviceType,
         deviceModels.modelName AS modelNumber,
         inventories.serialNumber AS serialNumber,
         inventories.macAddress AS macAddress
         FROM inventories 
         INNER JOIN deviceModels ON deviceModels.id = inventories.deviceModelId 
         INNER JOIN globalCodes ON globalCodes.id = deviceModels.deviceTypeId
         WHERE inventories.isAvailable = isAvailable AND deviceModels.deviceTypeId = deviceType;
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
        Schema::dropIfExists('procedure_of_inventoryList');
    }
}
