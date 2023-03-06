<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customFields', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->string('name');
            $table->string('type');
            $table->boolean('required');  
            $table->longText('properties');          
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);                    
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
        Schema::dropIfExists('customFields');
    }
}
