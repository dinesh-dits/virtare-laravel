<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('userId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('firstName',20);
            $table->string('lastName',20);
            $table->string('email');
            $table->string('phoneNumber');
            $table->biginteger('genderId')->unsigned();
            $table->foreign('genderId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('networkId')->unsigned();
            $table->foreign('networkId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('specializationId')->unsigned();
            $table->foreign('specializationId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('designationId')->unsigned();
            $table->foreign('designationId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('roleId')->unsigned()->nullable();
            $table->foreign('roleId')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade'); 
            $table->bigInteger('providerId')->unsigned()->nullable();
            $table->foreign('providerId')->references('id')->on('providers')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);
            $table->bigInteger('createdBy')->unsigned()->nullable();
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('updatedBy')->unsigned()->nullable();
            $table->foreign('updatedBy')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('deletedBy')->unsigned()->nullable();
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('createdAt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('staffs');
    }
}
