<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterAddRoleProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createRole = "DROP PROCEDURE IF EXISTS `createRole`";
        DB::unprepared($createRole);
        
        $createRole = 
        "CREATE PROCEDURE createRole(IN udid varchar(255), IN roles varchar(255),IN roleDescription text, IN roleTypeId varchar(30)) 
        BEGIN
        INSERT INTO accessRoles (udid,roles,roleDescription,roleTypeId) values(udid,roles,roleDescription,roleTypeId);
        END;";
        DB::unprepared($createRole);
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
