<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('changeLogs', function (Blueprint $table) {
            $table->id();
            $table->string('table');
            $table->bigInteger('tableId');
            $table->text('value');
            $table->enum('type',['created','updated','deleted','restored']);
            $table->string('ip');
            $table->boolean('isActive')->default(1);
            $table->boolean('isDeleted')->default(0);
            $table->timestamps();

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
        Schema::dropIfExists('change_logs');
    }
}
