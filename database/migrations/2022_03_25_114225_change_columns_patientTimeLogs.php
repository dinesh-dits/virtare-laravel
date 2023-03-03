<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsPatientTimeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientTimeLogs', function (Blueprint $table) {
            $table->bigInteger('categoryId')->unsigned()->nullable()->change();
            $table->bigInteger('cptCodeId')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientTimelogs', function (Blueprint $table) {
            //
        });
    }
}