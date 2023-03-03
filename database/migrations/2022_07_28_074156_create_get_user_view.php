<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGetUserView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $view = "DROP VIEW IF EXISTS `getUserDetails`";
        DB::unprepared($view);
        $view =
        'CREATE VIEW getUserDetails AS 
        SELECT users.id,users.email,patients.firstName,patients.middleName,patients.lastName,patients.phoneNumber,users.password,staffs.firstName AS staffFristName,staffs.lastName AS staffLastName,staffs.phoneNumber AS staffPhoneNumber
        FROM 
        `users`
        LEFT JOIN patients ON users.id = patients.userId
        LEFT JOIN staffs ON users.id = staffs.userId
        WHERE users.deletedAt IS NULL AND patients.deletedAt IS NULL AND staffs.deletedAt IS NULL
        ';

        DB::unprepared($view);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('get_user_view');
    }
}
