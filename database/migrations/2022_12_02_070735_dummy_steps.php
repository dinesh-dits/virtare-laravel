<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DummySteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DummySteps', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->biginteger('customFormAssignedId')->unsigned();
            $table->foreign('customFormAssignedId')->references('id')->on('customFormAssignedToUsers')->onDelete('cascade');
            $table->bigInteger('userId')->unsigned()->nullable();
            $table->bigInteger('assignedBy')->unsigned()->nullable();
            $table->integer('status')->unsigned()->nullable();
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
        //
    }
}
