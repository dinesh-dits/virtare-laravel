<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationEntityTypeIntoCptCodeNextBillingServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cptCodeNextBillingServices', function (Blueprint $table) {
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
        Schema::table('cptCodeNextBillingServices', function (Blueprint $table) {
            $table->dropColumn('locationEntityType');
        });
    }
}
