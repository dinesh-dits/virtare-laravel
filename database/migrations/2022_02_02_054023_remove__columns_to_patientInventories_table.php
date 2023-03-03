<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsToPatientInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientInventories', function (Blueprint $table) {
            $table->dropForeign('patientInventories_deviceType_foreign');
            $table->dropColumn('deviceType');
            $table->dropColumn('modelNumber');
            $table->dropColumn('serialNumber');
            $table->dropColumn('macAddress');
            $table->dropColumn('deviceTime');
            $table->dropColumn('serverTime');
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
            $table->bigInteger('deviceType')->unsigned();
            $table->foreign('deviceType')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
            $table->string('modelNumber');
            $table->string('serialNumber');
            $table->string('macAddress');
            $table->time('deviceTime');
            $table->time('serverTime');
        });
    }
}
