<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddGroupProgramProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `createGroupProgram`;";

        DB::unprepared($procedure);

        $procedure = 
          "CREATE PROCEDURE  createGroupProgram(IN udid varchar(255), IN providerId int,IN groupId int,IN programId int,IN createdBy int) 
            BEGIN
            INSERT INTO groupPrograms (udid,providerId,groupId,programId,createdBy) values(udid,providerId,groupId,programId,createdBy);
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
        Schema::dropIfExists('add_group_program_procedure');
    }
}
