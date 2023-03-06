<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StaffSpecializationTimelineProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `getStaffSpecializationCount`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `getStaffSpecializationCount`(fromDate VARCHAR(50),toDate VARCHAR(50))
        BEGIN
        SELECT count(specializationId) as total,
        globalCodes.name as text
       FROM staffs
        JOIN globalCodes ON staffs.specializationId = globalCodes.id
       WHERE  staffs.createdAt >= fromDate AND staffs.createdAt <= toDate AND staffs.deletedAt IS NULL 
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
        //
    }
}
