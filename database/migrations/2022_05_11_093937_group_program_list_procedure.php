<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GroupProgramListProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `groupProgramList`";
        DB::unprepared($procedure);
        $procedure =
        "CREATE PROCEDURE `groupProgramList`(IN Idx INT)
        BEGIN
        SELECT  groupPrograms.groupProgramId AS groupProgramId,groupPrograms.udid AS udid,programs.name,programs.udid AS programUdid
        FROM groupPrograms 
        JOIN programs ON groupPrograms.programId =  programs.id
        WHERE
       groupPrograms.groupId = Idx AND groupPrograms.deletedAt IS NULL AND programs.deletedAt IS NULL;
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