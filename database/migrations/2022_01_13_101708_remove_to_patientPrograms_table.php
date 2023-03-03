<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveToPatientProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientPrograms', function (Blueprint $table) {
            $table->dropColumn('startDate');
            $table->dropColumn('endDate');
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
            $table->date('startDate');
            $table->date('endDate');
        });
    }
}
