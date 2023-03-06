<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUpdateRoleProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $updateRole = "DROP PROCEDURE IF EXISTS `updateRole`";

        DB::unprepared($updateRole);

        $updateRole = "
        CREATE PROCEDURE  updateRole(
                                            IN id int,
                                            IN roles VARCHAR(50),
                                            IN roleDescription text,
                                            IN roleTypeId int,
                                            IN isActive TINYINT,
                                            IN updatedBy int
                                            ) 
        BEGIN
        UPDATE
        accessRoles
                    SET
                        roles = roles,
                        roleDescription = roleDescription,
                        roleTypeId = roleTypeId,
                        isActive = isActive,
                        updatedBy = updatedBy
                    WHERE
                        accessRoles.id = id;
                    END;";

        DB::unprepared($updateRole);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('updateRole_procedure');
    }
}
