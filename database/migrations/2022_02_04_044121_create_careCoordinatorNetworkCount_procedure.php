<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCareCoordinatorNetworkCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `careCoordinatorNetworkCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `careCoordinatorNetworkCount`()
        BEGIN
            SELECT count(networkId) as total,
            globalCodes.name as text,globalCodes.color as color
            FROM staffs
            JOIN globalCodes ON staffs.networkId = globalCodes.id
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
        Schema::dropIfExists('careCoordinatorNetworkCount_procedure');
    }
}
