<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsToCommunicationCallRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communicationCallRecords', function (Blueprint $table) {
            $table->dropForeign('communicationCallRecords_staffId_foreign');
            $table->dropColumn('staffId');
            $table->dropForeign('communicationCallRecords_callStatusId_foreign');
            $table->dropColumn('callStatusId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('communicationCallRecords', function (Blueprint $table) {
            $table->bigInteger('staffId');
            $table->foreign('staffId')->references('id')->on('staffs')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('callStatusId');
            $table->foreign('callStatusId')->references('id')->on('staffs')->onDelete('cascade')->onUpdate('cascade');
            
        });
    }
}
