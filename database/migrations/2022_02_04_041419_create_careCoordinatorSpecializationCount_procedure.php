<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCareCoordinatorSpecializationCountProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `careCoordinatorSpecializationCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `careCoordinatorSpecializationCount`()
        BEGIN
            SELECT count(specializationId) as total,
            globalCodes.name as text,globalCodes.color as color
            FROM staffs
            JOIN globalCodes ON staffs.specializationId = globalCodes.id
            GROUP BY (staffs.specializationId);
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
        Schema::dropIfExists('careCoordinatorSpecializationCount_procedure');
    }
}
