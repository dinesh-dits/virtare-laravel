<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationEntityTypeIntoEscalationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escalationDetails', function (Blueprint $table) {
            $table->string('locationEntityType')->default('Country')->after('providerLocationId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escalationDetails', function (Blueprint $table) {
            $table->dropColumn('locationEntityType');
        });
    }
}
