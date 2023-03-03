<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customTemplates', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->string('name')->nullable();
            $table->string('templateType')->nullable();
            $table->string('templateIcon')->nullable();
            $table->integer('order');
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);          
            $table->bigInteger('deletedBy')->unsigned()->nullable();
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable()->useCurrentOnUpdate();
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
        Schema::dropIfExists('customTemplates');
    }
}
