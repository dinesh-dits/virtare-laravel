<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCPTCodeProcedure5 extends Migration
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
            "CREATE PROCEDURE  createCPTCode(IN udid varchar(255), IN serviceId int,IN providerId int,IN providerLocationId int,IN entityType varchar(255),IN name varchar(255),
        IN billingAmout decimal(8,2), IN description text, IN durationId int) 
        BEGIN
        INSERT INTO cptCodes (udid,serviceId,providerId,providerLocationId,entityType,name,billingAmout,description,durationId) 
        values(udid,serviceId,providerId,providerLocationId,name,billingAmout,description,durationId);
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
        Schema::dropIfExists('c_p_t_code_procedure5');
    }
}
