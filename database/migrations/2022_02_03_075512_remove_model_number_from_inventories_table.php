<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveModelNumberFromInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('modelNumber');
            $table->bigInteger('deviceModelId')->unsigned()->after('udid');
            $table->foreign('deviceModelId')->references('id')->on('deviceModels')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('modelNumber',50);
            $table->dropColumn('deviceModelId');
        });
    }
}
