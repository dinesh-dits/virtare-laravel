<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixSearchProcedure extends Migration
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
            SELECT
            staffs.udid AS udid,
                            staffs.firstName AS firstName,
                            staffs.lastName AS lastName,
                            staffs.phoneNumber AS phoneNumber,
                            users.email AS email,
                            users.roleId AS type
                            FROM
                            staffs
                            LEFT JOIN users ON users.id = staffs.userId
                            WHERE
                            (staffs.firstName LIKE CONCAT('%', search , '%')) OR(staffs.lastName LIKE CONCAT('%', search , '%')) OR(staffs.phoneNumber LIKE CONCAT('%', search , '%')) OR(users.email LIKE CONCAT('%', search , '%'))
                            UNION
                            SELECT
                            patients.udid AS udid,
                            patients.firstName AS firstName,
                            patients.lastName AS lastName,
                            patients.phoneNumber AS phoneNumber,
                            users.email AS email,
                            users.roleId AS type
                            FROM
                            patients
                            LEFT JOIN users ON users.id = patients.userId
                            WHERE
                            (patients.firstName LIKE CONCAT('%', search , '%')) OR(patients.lastName LIKE CONCAT('%', search , '%')) OR(patients.phoneNumber LIKE CONCAT('%', search , '%')) OR(users.email LIKE CONCAT('%', search , '%')) OR
                            (patients.phoneNumber LIKE CONCAT('%', search , '%'))
                            OR
                            (users.email LIKE CONCAT('%', search , '%'));
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
