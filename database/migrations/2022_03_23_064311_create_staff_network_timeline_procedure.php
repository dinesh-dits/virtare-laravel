<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffNetworkTimelineProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getStaffNeworkCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `getStaffNeworkCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT count(networkId) as total,
        globalCodes.name as text
       FROM staffs
        JOIN globalCodes ON staffs.networkId = globalCodes.id
       WHERE  staffs.createdAt >= fromDate AND staffs.createdAt <= toDate AND staffs.deletedAt IS NULL  
       GROUP BY (staffs.networkId);
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
        Schema::dropIfExists('staff_network_timeline_procedure');
    }
}
