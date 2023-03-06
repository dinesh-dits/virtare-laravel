<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPaidToPatientTimeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientTimeLogs', function (Blueprint $table) {
            $table->boolean('isPaid')->default(0)->after('patientId');
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
            $table->dropColumn('isPaid');
        });
    }
}
