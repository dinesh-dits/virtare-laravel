<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPatientGoals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientGoals', function (Blueprint $table) {
            $table->bigInteger('deviceTypeId')->unsigned()->after('udid')->nullable();
            $table->foreign('deviceTypeId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->date('startDate')->after('deviceTypeId');
            $table->date('endDate')->after('startDate');
            $table->string('frequency')->after('endDate');
            $table->bigInteger('frequencyTypeId')->unsigned()->after('frequency');
            $table->foreign('frequencyTypeId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientGoals', function (Blueprint $table) {
            $table->dropForeign('patientGoals_deviceTypeId_foreign');
            $table->dropColumn('startDate');
            $table->dropColumn('endDate');
            $table->dropColumn('frequency');
            $table->dropForeign('patientGoals_frequencyTypeId_foreign');
        });
    }
}
