<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartDateIntoPatientInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientInsurances', function (Blueprint $table) {
            $table->string('startDate')->after('insuranceNameId')->nullable();
            $table->string('expirationDate')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientInsurances', function (Blueprint $table) {
            $table->dropColumn('startDate');
        });
    }
}
