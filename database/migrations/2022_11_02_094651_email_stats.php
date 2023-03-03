<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmailStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_stats', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('email');
            $table->string('status');
            $table->string('entity_type');
            $table->bigInteger('refrence_id');
            $table->timestamp('sent_on');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
            
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_stats');
    }
}
