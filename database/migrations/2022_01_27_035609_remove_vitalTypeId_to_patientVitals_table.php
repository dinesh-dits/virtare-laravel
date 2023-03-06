<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveVitalTypeIdToPatientVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->dropForeign('patientVitals_vitalTypeId_foreign');
            $table->dropColumn('vitalTypeId');
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
            $table->bigInteger('vitalTypeId')->unsigned();
            $table->foreign('vitalTypeId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
