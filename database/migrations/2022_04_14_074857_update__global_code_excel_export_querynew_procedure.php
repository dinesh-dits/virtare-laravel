<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateGlobalCodeExcelExportQuerynewProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procderGlobalCode = "DROP PROCEDURE IF EXISTS `globalCodeExcelExport`;";
        DB::unprepared($procderGlobalCode);
        $procderGlobalCode = "
            CREATE PROCEDURE  globalCodeExcelExport(search VARCHAR(100))
            BEGIN
            SELECT gc.*,gcc.name as globalCodeCategoryName 
            FROM `globalCodes` as gc 
            Left JOIN  globalCodeCategories as gcc on gcc.id = gc.globalCodeCategoryId
            WHERE gc.predefined = 0 
            AND (gc.name LIKE CONCAT('%',search,'%') OR gc.description LIKE CONCAT('%',search,'%')) 
            AND gc.deletedAt IS NULL
            ORDER BY `globalCodeCategoryName`  ASC;
            END;";
        DB::unprepared($procderGlobalCode);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('globalCodeExcelExport');
    }
}
