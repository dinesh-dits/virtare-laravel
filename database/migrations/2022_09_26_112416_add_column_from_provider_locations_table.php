<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnFromProviderLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerLocations', function (Blueprint $table) {
            $table->bigInteger('countryId')->unsigned()->nullable()->after('udid');
            $table->bigInteger('stateId')->unsigned()->nullable()->after('countryId');
            $table->string('city')->nullable()->after('stateId');
            $table->string('locationName')->nullable()->change();
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
            $table->dropColumn('countryId');
            $table->dropColumn('stateId');
            $table->dropColumn('city');
        });
    }
}
