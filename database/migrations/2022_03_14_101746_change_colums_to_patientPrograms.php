<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumsToPatientPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientPrograms', function (Blueprint $table) {
            $table->dateTime('onboardingScheduleDate')->change();
            $table->dateTime('dischargeDate')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientPrograms', function (Blueprint $table) {
            //
        });
    }
}
