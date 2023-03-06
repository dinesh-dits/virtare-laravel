<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsToPatientEmergencyContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientEmergencyContacts', function (Blueprint $table) {
            $table->string('fullName',30)->nullable()->change();
            $table->string('phoneNumber',20)->nullable()->change();
            $table->bigInteger('contactTypeId')->nullable()->unsigned()->change();
            $table->bigInteger('contactTimeId')->nullable()->unsigned()->change();
            $table->bigInteger('genderId')->nullable()->unsigned()->change();
            $table->string('email')->nullable()->change();
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
