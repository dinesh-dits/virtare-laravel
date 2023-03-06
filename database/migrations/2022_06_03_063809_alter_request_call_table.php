<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRequestCallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requestCalls', function (Blueprint $table) {
            $table->dropColumn('fromTime');
            $table->dropColumn('toTime');
            $table->dropColumn('timeZone');
            $table->bigInteger('contactTimeId')->after('userId');
            
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
            $table->string('fromTime')->after('userId');
            $table->string('toTime');
            $table->string('timeZone');
            $table->dropColumn('contactTiming');
            
        });
    }
}
