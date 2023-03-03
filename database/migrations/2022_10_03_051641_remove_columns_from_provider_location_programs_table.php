<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromProviderLocationProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerLocationPrograms', function (Blueprint $table) {
            $table->dropColumn('providerId');
            $table->dropColumn('providerLocationId');
            $table->dropColumn('subLocationId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providerLocationPrograms', function (Blueprint $table) {
            //
        });
    }
}
