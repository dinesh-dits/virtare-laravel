<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveToPatientPhysiciansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientPhysicians', function (Blueprint $table) {
            $table->dropForeign('patientPhysicians_staffId_foreign');
            $table->dropColumn('staffId');
            $table->dropColumn('isPrimary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientPhysicians', function (Blueprint $table) {
            $table->bigInteger('staffId')->unsigned();
            $table->foreign('staffId')->references('id')->on('staffs')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('isPrimary')->nullable();
        });
    }
}
