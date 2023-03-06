<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messageLogs', function (Blueprint $table) {
            $table->bigIncrements('messageLogId');
            $table->string('udid');
            $table->bigInteger('providerId')->default('1')->unsigned();
            $table->bigInteger('providerLocationId')->default('1')->unsigned();
            $table->bigInteger('programId')->default('5')->unsigned();
            $table->bigInteger('userId')->nullable();
            $table->string('status')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('subject')->nullable();
            $table->string('body')->nullable();
            $table->string('type')->nullable();
            $table->string('ip')->nullable();
            $table->bigInteger('referenceId')->nullable();
            $table->string('entityType')->nullable();
            $table->string('response')->nullable();
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);
            $table->bigInteger('createdBy')->unsigned()->nullable();
            $table->bigInteger('updatedBy')->unsigned()->nullable();
            $table->bigInteger('deletedBy')->unsigned()->nullable();
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
        Schema::dropIfExists('message_logs');
    }
}
