<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErrorlogWithDeviceInfoUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('errorLogWithDeviceInfo', function (Blueprint $table) {
            $table->text('errorMessage')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('errorLogWithDeviceInfo', function (Blueprint $table) {
            $table->text('errorMessage');
        });
    }
}
