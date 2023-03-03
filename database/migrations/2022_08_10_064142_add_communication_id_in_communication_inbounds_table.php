<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommunicationIdInCommunicationInboundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communicationInbounds', function (Blueprint $table) {
            $table->bigInteger('communicationId')->nullable()->after('udid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('communicationInbounds', function (Blueprint $table) {
            $table->dropColumn('communicationId');
        });
    }
}
