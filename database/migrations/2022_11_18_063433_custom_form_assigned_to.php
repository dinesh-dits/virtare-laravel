<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomFormAssignedTo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customFormAssignedToUsers', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->biginteger('customFormId')->unsigned();
            $table->foreign('customFormId')->references('id')->on('customForms')->onDelete('cascade');
            $table->bigInteger('userId')->unsigned()->nullable();
            $table->bigInteger('assignedBy')->unsigned()->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->bigInteger('deletedBy')->unsigned()->nullable();  
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customFormAssignedToUsers');
    }
}
