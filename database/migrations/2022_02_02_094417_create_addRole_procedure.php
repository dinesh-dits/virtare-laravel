<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddRoleProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createRole = "DROP PROCEDURE IF EXISTS `createRole`;
            CREATE PROCEDURE  createRole(IN udid varchar(255), IN roles varchar(255),IN roleDescription text, IN roleType varchar(30), IN masterLogin tinyint(1)) 
            BEGIN
            INSERT INTO roles (udid,roles,roleDescription,roleType,masterLogin) values(udid,roles,roleDescription,roleType,masterLogin);
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
        Schema::dropIfExists('addRole_procedure');
    }
}
