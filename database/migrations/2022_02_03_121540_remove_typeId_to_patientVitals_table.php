<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTypeIdToPatientVitalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientVitals', function (Blueprint $table) {
            $table->dropForeign('patientVitals_typeId_foreign');
            $table->dropColumn('typeId');
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
            $table->bigInteger('typeId')->unsigned();
            $table->foreign('typeId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
