<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderIdIntoEscalationFlagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escalationFlag', function (Blueprint $table) {
            $table->bigInteger('providerId')->default(1)->after('udid');
            $table->bigInteger('providerLocationId')->default(1)->after('providerId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escalationFlag', function (Blueprint $table) {
            $table->dropColumn('providerId');
            $table->dropColumn('providerLocationId');
        });
    }
}
