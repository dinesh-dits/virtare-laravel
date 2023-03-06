<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoCptCodeServiceConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cptCodeServiceConditions', function (Blueprint $table) {
            $table->date('startDate')->after('conditionId')->nullable();
            $table->date('endDate')->after('startDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cptCodeServiceConditions', function (Blueprint $table) {
            $table->dropColumn('startDate');
            $table->dropColumn('endDate');
        });
    }
}
