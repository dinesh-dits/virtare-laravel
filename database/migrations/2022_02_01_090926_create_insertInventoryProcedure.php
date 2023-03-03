<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInsertInventoryProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $screenAction = "DROP PROCEDURE IF EXISTS `createInventories`;
        CREATE PROCEDURE  createInventories(IN udid VARCHAR(255), IN deviceType int,IN modelNumber VARCHAR(50),IN serialNumber VARCHAR(50),IN macAddress VARCHAR(50),IN isActive TINYINT,IN createdBy int) 
        BEGIN
        INSERT INTO inventories (udid,deviceType,modelNumber,serialNumber,macAddress,isActive,createdBy) values(udid,deviceType,modelNumber,serialNumber,macAddress,isActive,createdBy);
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
        Schema::dropIfExists('insert_inventory_procedure');
    }
}
