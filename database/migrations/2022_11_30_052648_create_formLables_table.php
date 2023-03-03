<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFormLablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formLables', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->bigInteger('providerId')->default('1')->unsigned();
            $table->bigInteger('providerLocationId')->default('1')->unsigned();
            $table->bigInteger('programId')->default('5')->unsigned();
            $table->bigInteger('formId')->unsigned();
            $table->string('name')->nullable();
            $table->string('lableType')->nullable();
            $table->bigInteger('type')->nullable();
            $table->longText('description')->nullable();
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
        Schema::dropIfExists('formLables');
    }
}
