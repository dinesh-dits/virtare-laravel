<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCityIdIntoSubLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subLocations', function (Blueprint $table) {
            $table->bigInteger('cityId')->unsigned()->after('udid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subLocations', function (Blueprint $table) {
            $table->dropColumn('cityId');
        });
    }
}
