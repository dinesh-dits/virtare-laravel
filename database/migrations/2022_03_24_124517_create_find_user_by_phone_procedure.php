<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFindUserByPhoneProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `findUserByPhone`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `findUserByPhone`(phoneN INT(20))
        BEGIN
            SELECT firstName,lastName,phoneNumber,userId,u1.email FROM `patients` 
            LEFT JOIN users as u1 ON u1.id = patients.userId
            WHERE patients.phoneNumber LIKE phoneN
            UNION
            SELECT pt1.firstName,pt1.lastName,pt1.phoneNumber,pt1.userId,u1.email FROM `patientfamilymembers` 
            LEFT JOIN patients as pt1 ON pt1.id = patientfamilymembers.patientId
            LEFT JOIN users as u1 ON u1.id = patientfamilymembers.userId
            WHERE patientfamilymembers.phoneNumber LIKE phoneN
            UNION
            SELECT firstName,lastName,phoneNumber,userId,u1.email FROM `staffs`
            LEFT JOIN users as u1 ON u1.id = staffs.userId
            WHERE staffs.phoneNumber LIKE phoneN limit 1;
        END;";
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('findUserByPhone');
    }
}
