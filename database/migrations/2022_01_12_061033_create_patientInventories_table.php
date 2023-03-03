<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patientInventories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventoryId')->unsigned();
            $table->foreign('inventoryId')->references('id')->on('inventories')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('patientId')->unsigned();
            $table->foreign('patientId')->references('id')->on('patients')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('deviceType')->unsigned();
            $table->foreign('deviceType')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('modelNumber',50);
            $table->string('serialNumber',50);
            $table->string('macAddress',50);
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
        Schema::dropIfExists('patient_inventories');
    }
}
