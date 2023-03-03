<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSameAsPrimaryPatientEmergencyContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientEmergencyContacts', function (Blueprint $table) {
            $table->boolean('sameAsPrimary')->after('udid');
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
            //
        });
    }
}
