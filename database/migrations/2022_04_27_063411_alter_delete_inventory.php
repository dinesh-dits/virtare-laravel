<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDeleteInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $screenAction = "DROP PROCEDURE IF EXISTS `deleteInventory`;";

        DB::unprepared($screenAction);

        $screenAction = "
        CREATE PROCEDURE  deleteInventory(IN idx int,IN inventoryIdx int) 
        BEGIN
        UPDATE `inventories` SET `isDelete`='1',`deletedBy`=idx,`deletedAt`=CURRENT_TIMESTAMP WHERE inventories.id = inventoryIdx;
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
        //
    }
}
