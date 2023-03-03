<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePatientIdToCommunicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->dropForeign('communications_patientId_foreign');
            $table->dropColumn('patientId');
            $table->dropColumn('to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->bigInteger('patientId')->unsigned();
            $table->foreign('patientId')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
            $table->string('to');

        });
    }
}
