<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffSpecializationNewProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getStaffSpecializationCountNew`;
        CREATE PROCEDURE `getStaffSpecializationCountNew`(timelineStartDate INT(20),timelineEndDate INT(20))
        BEGIN
        SELECT count(specializationId) as total, globalCodes.name as text
        FROM staffs
        JOIN globalCodes ON staffs.specializationId = globalCodes.id
        WHERE (staffs.createdAt BETWEEN timelineStartDate AND timelineEndDate)
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
        Schema::dropIfExists('staff_specialization_new_procedure');
    }
}
