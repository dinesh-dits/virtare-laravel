<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SearchProcedure1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $search = "DROP PROCEDURE IF EXISTS `search`;";
        DB::unprepared($search);
        $search = "
        CREATE PROCEDURE  search(IN search VARCHAR(100)) 
        BEGIN
        SELECT staffs.udid AS udid, CONCAT(staffs.firstName, ' ',staffs.lastName) AS fullName, staffs.firstName AS firstName,
        staffs.lastName AS lastName, staffs.phoneNumber AS phoneNumber, users.email AS email, users.roleId AS type,'' AS middleName , staffs.deletedAt
        FROM
        staffs
        LEFT JOIN users ON users.id = staffs.userId
        WHERE staffs.deletedAt IS NULL
        HAVING
        (staffs.firstName LIKE CONCAT('%', search ,'%')) OR(staffs.lastName LIKE CONCAT('%', search ,'%')) OR (CONCAT(staffs.firstName, ' ', staffs.lastName))  LIKE CONCAT('%', search ,'%')
        OR (CONCAT(staffs.lastName, ' ', staffs.firstName))  LIKE CONCAT('%', search ,'%') 	OR(staffs.phoneNumber LIKE CONCAT('%', search ,'%')) 
        OR(users.email LIKE CONCAT('%', search ,'%')) AND staffs.deletedAt IS NULL
        UNION
        SELECT patients.udid AS udid, CONCAT( patients.firstName, ' ',patients.middleName, ' ',patients.lastName) AS fullName, patients.firstName AS firstName,
        patients.lastName AS lastName, patients.phoneNumber AS phoneNumber, users.email AS email, users.roleId AS type, patients.middleName AS middleName, patients.deletedAt
        FROM
        patients
        LEFT JOIN users ON users.id = patients.userId
        WHERE patients.deletedAt IS NULL
        HAVING
        (patients.firstName LIKE CONCAT('%', search ,'%')) OR(patients.lastName LIKE CONCAT('%', search ,'%')) OR (CONCAT(patients.firstName, ' ', patients.middleName, ' ', patients.lastName)) LIKE CONCAT('%', search ,'%') 
        OR (CONCAT(patients.firstName,' ', patients.lastName)) LIKE CONCAT('%', search ,'%') OR (CONCAT(patients.lastName,' ', patients.firstName)) LIKE CONCAT('%', search ,'%') 
        OR (CONCAT(patients.firstName,' ', patients.middleName)) LIKE CONCAT('%', search ,'%')  OR (CONCAT(patients.middleName,' ', patients.firstName)) LIKE CONCAT('%', search ,'%') 
        OR (CONCAT(patients.lastName,' ', patients.middleName)) LIKE CONCAT('%', search ,'%')  OR (CONCAT(patients.middleName,' ', patients.lastName)) LIKE CONCAT('%', search ,'%') 
	    OR (CONCAT(patients.lastName, ' ', patients.middleName, ' ', patients.firstName)) LIKE CONCAT('%', search ,'%') 
        OR(patients.phoneNumber LIKE CONCAT('%', search ,'%')) OR(users.email LIKE CONCAT('%', search ,'%'));
        END;";
        DB::unprepared($search);
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
