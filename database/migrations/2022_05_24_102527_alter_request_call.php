<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRequestCall extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requestCalls', function (Blueprint $table) {
            $table->dropColumn('contactTiming');
            $table->string('fromTime')->after('userId');
            $table->string('toTime')->after('fromTime');
            $table->string('timeZone')->after('toTime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requestCalls', function (Blueprint $table) {
            $table->string('contactTiming')->after('userId');
            $table->dropColumn('fromTime');
            $table->dropColumn('toTime');
            $table->dropColumn('timeZone');
        });
    }
}
