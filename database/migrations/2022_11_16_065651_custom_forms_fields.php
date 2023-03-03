<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomFormsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customFormFields', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->biginteger('customFormId')->unsigned();
            $table->foreign('customFormId')->references('id')->on('customForms')->onDelete('cascade');
            $table->integer('order');
            $table->string('name');
            $table->string('type');
            $table->tinyInteger('required');
            $table->text('properties');    
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
        Schema::dropIfExists('customFormFields');
    }
}
