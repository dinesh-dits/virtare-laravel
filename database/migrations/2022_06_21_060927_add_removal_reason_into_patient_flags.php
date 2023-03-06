<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemovalReasonIntoPatientFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientFlags', function (Blueprint $table) {
            $table->bigInteger('removalReasonId')->after('flagId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientFlags', function (Blueprint $table) {
            $table->dropColumn('removalReasonId');
        });
    }
}
