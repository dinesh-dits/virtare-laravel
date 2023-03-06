<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePatientIdToVitalFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vitalFields', function (Blueprint $table) {
            $table->dropForeign('vitalFields_patientId_foreign');
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
        Schema::table('vitalFields', function (Blueprint $table) {
            $table->bigInteger('patientId')->unsigned();
            $table->foreign('patientId')->references('id')->on('vitalFields')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
