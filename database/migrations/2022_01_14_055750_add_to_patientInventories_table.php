<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToPatientInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientInventories', function (Blueprint $table) {
            $table->time('deviceTime')->after('macAddress');
            $table->time('serverTime')->after('deviceTime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientInventories', function (Blueprint $table) {
            $table->dropColumn('deviceTime');
            $table->dropColumn('serverTime');
        });
    }
}
