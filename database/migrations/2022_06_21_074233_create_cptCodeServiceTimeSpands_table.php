<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCptCodeServiceTimeSpandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cptCodeServiceTimeSpands', function (Blueprint $table) {
            $table->id();
            $table->string('udid')->nullable();
            $table->bigInteger('cptCodeServicesDetailId')->nullable();
            $table->timestamp('timeSpand')->nullable();
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
        Schema::dropIfExists('cptCodeServiceTimeSpands');
    }
}