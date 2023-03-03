<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterInventoryListProcedure extends Migration
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
        SELECT inventories.* ,
            globalCodes.name as deviceType,
            inventories.udid as udid
            FROM inventories
            JOIN globalCodes ON inventories.deviceType = globalCodes.id
            WHERE inventories.isAvailable = isAvailable AND inventories.deviceType = deviceType;
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
