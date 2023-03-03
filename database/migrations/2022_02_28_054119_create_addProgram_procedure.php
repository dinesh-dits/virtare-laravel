<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddProgramProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createProgram = "DROP PROCEDURE IF EXISTS `createProgram`;";

        DB::unprepared($createProgram);

        $createProgram = 
       "CREATE PROCEDURE  createProgram(IN udid varchar(255), IN typeId int,IN description text) 
        BEGIN
        INSERT INTO programs (udid,typeId,description) values(udid,typeId,description);
        END;";

    DB::unprepared($createProgram);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addProgram_procedure');
    }
}
