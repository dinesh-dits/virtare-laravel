<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveToPatientEmergencyContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientEmergencyContacts', function (Blueprint $table) {
            $table->dropForeign('patientEmergencyContacts_userId_foreign');
            $table->dropColumn('userId');
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
            $table->bigInteger('userId')->unsigned();
            $table->foreign('userId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
