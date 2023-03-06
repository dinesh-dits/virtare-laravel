<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('firstName',25);
            $table->string('middleName',25)->nullable();
            $table->string('lastName',25)->nullable();
            $table->date('dob');
            $table->bigInteger('genderId')->unsigned();
            $table->foreign('genderId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('languageId')->unsigned();
            $table->foreign('languageId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('otherLanguageId')->nullable()->unsigned();
            $table->foreign('otherLanguageId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('nickName',25)->nullable();
            $table->string('height',10)->nullable();
            $table->string('weight',10)->nullable();
            $table->bigInteger('userId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('phoneNumber',20);
            $table->bigInteger('contactTypeId')->unsigned();
            $table->foreign('contactTypeId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('contactTimeId')->unsigned();
            $table->foreign('contactTimeId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('medicalRecordNumber',30);
            $table->biginteger('countryId')->unsigned();
            $table->foreign('countryId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->biginteger('stateId')->unsigned();
            $table->foreign('stateId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('city',50);
            $table->string('zipCode',10);
            $table->string('appartment',20);
            $table->string('address',200);
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
        Schema::dropIfExists('patients');
    }
}
