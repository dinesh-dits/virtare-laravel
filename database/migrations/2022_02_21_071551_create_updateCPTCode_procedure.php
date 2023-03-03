<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUpdateCPTCodeProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $updateCPTCode = "DROP PROCEDURE IF EXISTS `updateCPTCode`;";

        DB::unprepared($updateCPTCode);

        $updateCPTCode =
        " CREATE PROCEDURE  updateCPTCode(
                                            IN id int,
                                            IN serviceId int,
                                            IN providerId int,
                                            IN name VARCHAR(225),
                                            IN billingAmout decimal(8,2),
                                            IN description text, 
                                            IN durationId int,
                                            IN isActive TINYINT,
                                            IN updatedBy int
                                            ) 
        BEGIN
        UPDATE
        cptCodes
                    SET
                        serviceId = serviceId,
                        providerId = providerId,
                        name = name,
                        billingAmout = billingAmout,
                        description = description,
                        durationId = durationId,
                        isActive = isActive,
                        updatedBy = updatedBy
                    WHERE
                        cptCodes.id = id;
                    END;";

        DB::unprepared($updateCPTCode);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('updateCPTCode_procedure');
    }
}
