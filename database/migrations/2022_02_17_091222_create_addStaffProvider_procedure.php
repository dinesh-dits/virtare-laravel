<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddStaffProviderProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createStaffProvider = "DROP PROCEDURE IF EXISTS `createStaffProvider`;";

        DB::unprepared($createStaffProvider);

        $createStaffProvider = 
       "CREATE PROCEDURE  createStaffProvider(IN udid varchar(255), IN staffId int,IN providerId int) 
        BEGIN
        INSERT INTO staffProviders (udid,staffId,providerId) values(udid,staffId,providerId);
        END;";

    DB::unprepared($createStaffProvider);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addStaffProvider_procedure');
    }
}
