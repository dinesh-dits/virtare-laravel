<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientResponseProgramProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `addClientResponseProgram`";
        DB::unprepared($procedure);
        $procedure =
            'CREATE PROCEDURE `addClientResponseProgram`(IN data TEXT)
        BEGIN
        INSERT INTO clientResponsePrograms
        (udid,clientResponseAnswerId,program,createdBy) 
        values
        (JSON_UNQUOTE(JSON_EXTRACT(data, "$.udid")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.clientResponseAnswerId")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.program")),JSON_UNQUOTE(JSON_EXTRACT(data, "$.createdBy")));
        SELECT * FROM clientResponsePrograms WHERE clientResponseProgramId =LAST_INSERT_ID();  
        END;';
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
