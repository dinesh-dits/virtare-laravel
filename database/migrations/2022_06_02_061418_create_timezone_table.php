<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimezoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('timezone')) {
            Schema::create('timezone', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('udid');
                $table->string('countryCode')->nullable();
                $table->string('timeZone')->nullable();
                $table->string('UTCOffset')->nullable(); 
                $table->string('UTCDSTOffset')->nullable();
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timezone');
    }
}
