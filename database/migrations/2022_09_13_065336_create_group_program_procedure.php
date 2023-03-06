<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupProgramProcedure extends Migration
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
            "CREATE PROCEDURE  createGroupProgram(IN udid varchar(255), IN providerId int, IN providerLocationId int,IN groupId int,IN programId int,IN createdBy int) 
            BEGIN
            INSERT INTO groupPrograms (udid,providerId,providerLocationId,groupId,programId,createdBy) values(udid,providerId,providerLocationId,groupId,programId,createdBy);
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
        Schema::dropIfExists('group_program_procedure');
    }
}
