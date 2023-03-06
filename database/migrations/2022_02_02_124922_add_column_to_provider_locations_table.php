<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProviderLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerLocations', function (Blueprint $table) {
            $table->string('numberOfLocations')->after('locationName');
            $table->boolean('isDefault')->nullable()->after('websiteUrl');
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
            $table->dropColumn('numberOfLocations');
            $table->dropColumn('isDefault');
        });
    }
}
