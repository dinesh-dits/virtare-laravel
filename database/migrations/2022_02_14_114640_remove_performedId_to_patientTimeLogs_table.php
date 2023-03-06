<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePerformedIdToPatientTimeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientTimeLogs', function (Blueprint $table) {
            $table->dropForeign('patientTimeLogs_performedId_foreign');
            $table->dropColumn('performedId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientTimeLogs', function (Blueprint $table) {
            $table->bigInteger('performedId')->unsigned();
            $table->foreign('performedId')->references('id')->on('patients')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
