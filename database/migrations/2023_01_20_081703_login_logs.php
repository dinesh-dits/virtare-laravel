<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LoginLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loginLogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('udid');
            $table->string('login_id'); 
            $table->string('platform'); 
            $table->string('browser'); 
            $table->string('ip_address'); 
            $table->string('type'); 
            $table->timestamp('date'); 
            $table->string('attempt'); 
            $table->text('message');           
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
