<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTimeSpandDataTypeInCptCodeServiceTimeSpandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cptCodeServiceTimeSpents', function (Blueprint $table) {
            $table->bigInteger('timeSpent')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cptCodeServiceTimeSpents', function (Blueprint $table) {
            $table->dropColumn('timeSpent');
        });
    }
}
