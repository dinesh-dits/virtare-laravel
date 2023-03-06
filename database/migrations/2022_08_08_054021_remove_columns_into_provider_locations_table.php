<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsIntoProviderLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerLocations', function (Blueprint $table) {
            $table->dropColumn('numberOfLocations');
            $table->dropColumn('zipCode');
            $table->dropColumn('stateId');
            $table->dropColumn('city');
            $table->dropColumn('locationAddress');
            $table->dropColumn('phoneNumber');
            $table->dropColumn('email');
            $table->dropColumn('websiteUrl');
            $table->dropColumn('isDefault');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providerLocations', function (Blueprint $table) {
            //
        });
    }
}
