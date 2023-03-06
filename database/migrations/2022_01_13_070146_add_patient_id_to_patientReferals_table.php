<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPatientIdToPatientReferalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientReferals', function (Blueprint $table) {
            $table->bigInteger('patientId')->after('email')->unsigned();
            $table->foreign('patientId')->references('id')->on('patients')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientReferals', function (Blueprint $table) {
            $table->dropColumn('patientId');
            $table->foreign('patientId')->references('id')->on('patients')->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
