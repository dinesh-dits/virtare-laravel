<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCommunicationCallRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communicationCallRecords', function (Blueprint $table) {
            $table->bigInteger('statusId')->after('udid')->unsigned();
            $table->foreign('statusId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
            $table->time('startTime')->after('statusId');
            $table->time('endTime')->after('startTime');
            $table->bigInteger('referenceId')->after('endTime');
            $table->string('entityType')->after('referenceId');
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
            $table->dropForeign('communicationCallRecords_statusId_foreign');
            $table->dropColumn('statusId');
            $table->dropColumn('startTime');
            $table->dropColumn('endTime');
            $table->dropColumn('referenceId')->unsigned();
            $table->dropColumn('entityType');
        });
    }
}
