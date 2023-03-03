<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlaceofserviceIntoCptCodeServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cptCodeServices', function (Blueprint $table) {
            $table->bigInteger('placeOfService')->after('cost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cptCodeServices', function (Blueprint $table) {
            $table->dropColumn('placeOfService');
        });
    }
}
