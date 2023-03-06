<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixInventoryListProcedure extends Migration
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
            "CREATE PROCEDURE `inventoryList`(IN isAvailable TINYINT,IN deviceType INT,IN active INT,IN search VARCHAR(100))
       BEGIN
        SELECT
        inventories.*,
        inventories.udid AS udid,
        deviceModels.modelName AS modelNumber,
        globalCodes.name AS deviceType,
        globalCodes.id AS deviceTypeId,
        deviceModels.modelName AS modelNumber,
        inventories.serialNumber AS serialNumber,
        inventories.macAddress AS macAddress,
        inventories.isAvailable AS isAvailable
        FROM inventories  
        INNER JOIN deviceModels ON deviceModels.id = inventories.deviceModelId 
        INNER JOIN globalCodes ON globalCodes.id = deviceModels.deviceTypeId
        WHERE (inventories.isAvailable = isAvailable OR isAvailable='')
        AND (inventories.isActive=active OR active='')
        AND (deviceModels.deviceTypeId = deviceType OR deviceType='')
        AND ((globalCodes.name LIKE CONCAT('%',search,'%'))
        OR (deviceModels.modelName LIKE CONCAT('%',search,'%')));
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
