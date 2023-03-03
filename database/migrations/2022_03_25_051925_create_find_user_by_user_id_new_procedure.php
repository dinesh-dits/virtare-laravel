<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFindUserByUserIdNewProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "DROP PROCEDURE IF EXISTS `findUserByUserId`";
        DB::unprepared($procedure);
        $procedure =
            "CREATE PROCEDURE `findUserByUserId`(userIds INT(20))
        BEGIN
        SELECT firstName,lastName,phoneNumber,userId,u1.email,u1.udid FROM `patients` 
            LEFT JOIN users as u1 ON u1.id = patients.userId
            WHERE patients.userId = userIds
            UNION
            SELECT pt1.firstName,pt1.lastName,pt1.phoneNumber,pt1.userId,u1.email,u1.udid FROM `patientFamilyMembers` 
            LEFT JOIN patients as pt1 ON pt1.id = patientFamilyMembers.patientId
            LEFT JOIN users as u1 ON u1.id = patientFamilyMembers.userId
            WHERE pt1.userId = userIds
            UNION
            SELECT firstName,lastName,phoneNumber,userId,u1.email,u1.udid FROM `staffs`
            LEFT JOIN users as u1 ON u1.id = staffs.userId
            WHERE staffs.userId  = userIds limit 1;
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
        Schema::dropIfExists('find_user_by_user_id_new_procedure');
    }
}
