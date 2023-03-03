<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePatientIdToPatientVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->dropForeign('patientVitals_patientId_foreign');
            $table->dropColumn('patientId');
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
            $table->bigInteger('patientId')->unsigned();
            $table->foreign('patientId')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
