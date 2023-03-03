<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumsToPatentMedicineRoutines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patentMedicineRoutines', function (Blueprint $table) {
            $table->dateTime('startDate')->change();
            $table->dateTime('endDate')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patentMedicineRoutines', function (Blueprint $table) {
            //
        });
    }
}
