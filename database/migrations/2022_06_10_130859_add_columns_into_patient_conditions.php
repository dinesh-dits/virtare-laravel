<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoPatientConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientConditions', function (Blueprint $table) {
            $table->date('startDate')->nullable()->after('udid');
            $table->date('endDate')->nullable()->after('startDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientConditions', function (Blueprint $table) {
            $table->dropColumn('startDate');
            $table->dropColumn('endDate');
        });
    }
}
