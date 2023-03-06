<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderIntoPatientReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientReferrals', function (Blueprint $table) {
            $table->bigInteger('providerLocationId')->default('1')->after('providerId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientReferrals', function (Blueprint $table) {
            $table->dropColumn('providerLocationId');
        });
    }
}
