<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoProviderLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerLocations', function (Blueprint $table) {
            $table->integer('level')->after('locationName')->nullable();
            $table->bigInteger('parent')->after('level')->nullable();
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
            $table->dropColumn('level');
            $table->dropColumn('parent');
        });
    }
}
