<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRoleDeleteProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $roleDelete = "DROP PROCEDURE IF EXISTS `deleteRole`;";

        DB::unprepared($roleDelete);

        $roleDelete = "
        CREATE PROCEDURE  deleteRole(IN id int,IN isDelete TINYINT,IN deletedBy int,IN deletedAt timestamp) 
        BEGIN
        UPDATE
        accessRoles
        SET
        isDelete = isDelete,
        deletedBy = deletedBy,
        deletedAt = deletedAt
        WHERE
        accessRoles.id = id;
        END;";
        

        DB::unprepared($roleDelete);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roleDelete_procedure');
    }
}
