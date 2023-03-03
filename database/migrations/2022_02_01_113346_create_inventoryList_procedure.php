<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `inventoryList`;
        CREATE PROCEDURE `inventoryList`()
        BEGIN
        SELECT * ,
            globalCodes.name as deviceType,
            inventories.udid as udid
            FROM inventories
            JOIN globalCodes ON inventories.deviceType = globalCodes.id;
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
        Schema::dropIfExists('inventory_list_procedure');
    }
}
