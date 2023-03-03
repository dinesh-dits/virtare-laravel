<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVitalFieldIdToPatientVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->bigInteger('vitalFieldId')->unsigned()->after('patientId');
            $table->foreign('vitalFieldId')->references('id')->on('vitalFields')->onDelete('cascade')->onUpdate('cascade');
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
            $table->dropForeign('patientVitals_vitalFieldId_foreign');
            $table->dropColumn('vitalFieldId');
        });
    }
}
