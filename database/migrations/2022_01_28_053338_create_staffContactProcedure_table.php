<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStaffContactProcedureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createStaffContact = "DROP PROCEDURE IF EXISTS `createStaffContact`;
            CREATE PROCEDURE  createStaffContact(IN udid varchar(255), IN firstName varchar(20),IN lastName varchar(20), IN email varchar(50), IN phoneNumber varchar(20), IN staffId int) 
            BEGIN
            INSERT INTO staffContacts (udid,firstName,lastName,email,phoneNumber,staffId) values(udid,firstName,lastName,email,phoneNumber,staffId);
            END;";
  
        DB::unprepared($createStaffContact);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staffContactProcedure');
    }
}
