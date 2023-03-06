<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefultHighLowVitalField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vitalFields', function (Blueprint $table) {
            $table->string('high')->after('icon');
            $table->string('low')->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vitalFields', function (Blueprint $table) {
            $table->dropColumn('high');
            $table->dropColumn('low');
        });
    }
}
