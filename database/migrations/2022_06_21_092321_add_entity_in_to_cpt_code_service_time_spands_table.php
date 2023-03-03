<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEntityInToCptCodeServiceTimeSpandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cptCodeServiceTimeSpands', function (Blueprint $table) {
            $table->bigInteger('refrenceId')->after('timeSpent')->nullable();
            $table->string('entity')->after('refrenceId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cptCodeServiceTimeSpands', function (Blueprint $table) {
            $table->dropColumn('refrenceId');
            $table->dropColumn('entity');
        });
    }
}
