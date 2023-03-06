<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NullableColumnsToPatientVitals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->string('units')->nullable()->change();
            $table->dateTime('startTime')->nullable()->change();
            $table->dateTime('endTime')->nullable()->change();
            $table->string('createdType')->nullable()->change();
            $table->string('deviceInfo')->nullable()->change();
            $table->string('createdType')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            //
        });
    }
}
