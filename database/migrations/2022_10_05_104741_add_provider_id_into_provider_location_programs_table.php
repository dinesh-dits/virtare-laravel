<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderIdIntoProviderLocationProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerLocationPrograms', function (Blueprint $table) {
            $table->bigInteger('providerId')->unsigned()->after('udid')->default(1);
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
            $table->dropColumn('providerId');
        });
    }
}
