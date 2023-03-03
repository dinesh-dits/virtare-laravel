<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CareCoordinatorSpecializationCountProcedure1 extends Migration
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
        "CREATE PROCEDURE `careCoordinatorSpecializationCount`(staffIdx INT)
        BEGIN
        SELECT(IF((staffs.createdAt IS NULL),0,COUNT(staffs.id))) AS total,
        globalCodes.name AS text,globalCodes.color as color
        FROM
        staffs
        RIGHT JOIN globalCodes ON staffs.specializationId = globalCodes.id
        WHERE globalCodes.globalCodeCategoryId = 2 AND staffs.deletedAt IS NULL AND globalCodes.deletedAt IS NULL AND (staffs.id=staffIdx OR staffIdx='')
        GROUP BY globalCodes.id;
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
