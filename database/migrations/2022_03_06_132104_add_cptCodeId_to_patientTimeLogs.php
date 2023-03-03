<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCptCodeIdToPatientTimeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientTimeLogs', function (Blueprint $table) {
            $table->bigInteger('cptCodeId')->after('udid')->nullable();
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
            $table->dropColumn('cptCodeId');
        });
    }
}
