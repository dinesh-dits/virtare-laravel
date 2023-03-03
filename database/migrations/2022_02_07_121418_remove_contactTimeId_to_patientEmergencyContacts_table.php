<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveContactTimeIdToPatientEmergencyContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientEmergencyContacts', function (Blueprint $table) {
            $table->dropForeign('patientEmergencyContacts_contactTimeId_foreign');
            $table->dropColumn('contactTimeId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientEmergencyContacts', function (Blueprint $table) {
            $table->bigInteger('contactTimeId')->unsigned();
            $table->foreign('contactTimeId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
