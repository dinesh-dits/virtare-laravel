<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryUpdateFieldsProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $screenAction = "DROP PROCEDURE IF EXISTS `updateInventory`;
        CREATE PROCEDURE  updateInventory(
                                            IN id int,
                                            IN deviceModelId VARCHAR(50),
                                            IN serialNumber VARCHAR(50),
                                            IN macAddress VARCHAR(50),
                                            IN isActive TINYINT,
                                            IN updatedBy int
                                            ) 
        BEGIN
        UPDATE
        inventories
                    SET
                        deviceModelId = deviceModelId,
                        serialNumber = serialNumber,
                        macAddress = macAddress,
                        isActive = isActive,
                        updatedBy = updatedBy
                    WHERE
                        inventories.id = id;
                    END;";

        DB::unprepared($screenAction);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_update_fields_procedure');
    }
}
