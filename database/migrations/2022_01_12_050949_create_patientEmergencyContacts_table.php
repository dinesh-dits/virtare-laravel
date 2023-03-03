<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientEmergencyContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patientEmergencyContacts', function (Blueprint $table) {
            $table->id();
            $table->string('fullName',30);
            $table->bigInteger('userId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('phoneNumber',20);
            $table->bigInteger('contactTypeId')->unsigned();
            $table->foreign('contactTypeId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('contactTimeId')->unsigned();
            $table->foreign('contactTimeId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('genderId')->unsigned();
            $table->foreign('genderId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('patientId')->unsigned();
            $table->foreign('patientId')->references('id')->on('patients')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('isPrimary')->nullable();
            $table->boolean('sameAsFamily')->nullable();
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
        Schema::dropIfExists('patient_emergency_contacts');
    }
}
