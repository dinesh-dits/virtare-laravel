<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenewalDateColumnInPatientProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientPrograms', function (Blueprint $table) {
            $table->datetime('renewalDate')->nullable()->after('dischargeDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientPrograms', function (Blueprint $table) {
            $table->dropColumn('renewalDate');
        });
    }
}
