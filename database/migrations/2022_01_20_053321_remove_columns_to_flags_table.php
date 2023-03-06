<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsToFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('flags', function (Blueprint $table) {
            $table->dropForeign('flags_colorId_foreign');
            $table->dropColumn('colorId');
            $table->dropColumn('rules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flags', function (Blueprint $table) {
            $table->bigInteger('colorId')->unsigned();
            $table->foreign('colorId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
            $table->text('colorId');

        });
    }
}
