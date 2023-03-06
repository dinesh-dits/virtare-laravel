<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIntoStaffLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staffLocations', function (Blueprint $table) {
            $table->text('locationsHierarchy')->after('providerLocationId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staffLocations', function (Blueprint $table) {
            $table->dropColumn('locationsHierarchy');
        });
    }
}
