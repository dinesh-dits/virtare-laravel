<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffContactProcedure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $createStaffContact = "DROP PROCEDURE IF EXISTS `createStaffContact`;
        CREATE PROCEDURE  createStaffContact(IN providerId bigInt,IN udid varchar(255), IN firstName varchar(255), IN middleName varchar(255),IN lastName varchar(255), IN extension varchar(20), IN email varchar(50), IN phoneNumber varchar(20), IN staffId int) 
        BEGIN
        INSERT INTO staffContacts (providerId,udid,firstName,middleName,lastName,extension,email,phoneNumber,staffId) values(providerId,udid,firstName,middleName,lastName,extension,email,phoneNumber,staffId);
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
        Schema::dropIfExists('staff_contact_procedure2');
    }
}
