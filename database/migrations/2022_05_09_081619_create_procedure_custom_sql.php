<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcedureCustomSql extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = 'DROP PROCEDURE IF EXISTS `customSql`;';

        DB::unprepared($procedure);

        $procedure ='CREATE PROCEDURE  customSql(IN queryString TEXT) 
        BEGIN
            SET @queryString =queryString;
            PREPARE stmt FROM @queryString;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
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
        Schema::dropIfExists('customSql');
    }
}
