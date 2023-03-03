<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeSpendInToCptCodeServiceTimeSpandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cptCodeServiceTimeSpands', function (Blueprint $table) {
            $table->dropColumn('timeSpand');
            $table->time('timeSpent')->after('cptCodeServicesDetailId')->nullable();

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
            $table->timestamp('timeSpand')->after('cptCodeServicesDetailId')->nullable();
            $table->dropColumn('timeSpent');
        });
    }
}
