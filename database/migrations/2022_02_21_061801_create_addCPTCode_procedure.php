<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddCPTCodeProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createCPTCode = "DROP PROCEDURE IF EXISTS `createCPTCode`;";

        DB::unprepared($createCPTCode);

        $createCPTCode = 
       "CREATE PROCEDURE  createCPTCode(IN udid varchar(255), IN serviceId int,IN providerId int,IN name varchar(255),
        IN billingAmout decimal(8,2), IN description text, IN durationId int) 
        BEGIN
        INSERT INTO cptCodes (udid,serviceId,providerId,name,billingAmout,description,durationId) 
        values(udid,serviceId,providerId,name,billingAmout,description,durationId);
        END;";

    DB::unprepared($createCPTCode);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addCPTCode_procedure');
    }
}
