<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->string('email',100)->unique();
            $table->string('password');            
            $table->timestamp('emailVerifiedAt')->nullable();
            $table->boolean('emailVerify');
            $table->bigInteger('roleId')->unsigned()->nullable();
            $table->foreign('roleId')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');
            $table->rememberToken();
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);
            $table->bigInteger('createdBy')->unsigned()->nullable();
            $table->bigInteger('updatedBy')->unsigned()->nullable();
            $table->bigInteger('deletedBy')->unsigned()->nullable();
            $table->foreign('createdBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('updatedBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('deletedBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->timestamp('deletedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
