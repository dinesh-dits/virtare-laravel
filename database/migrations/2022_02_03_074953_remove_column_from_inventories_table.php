<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnFromInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign('inventories_deviceType_foreign');
            $table->dropColumn('deviceType');
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
            $table->bigInteger('deviceType')->unsigned();
            $table->foreign('deviceType')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
