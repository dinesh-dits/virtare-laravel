<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMiddleNameIntoPatientFamilyMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientFamilyMembers', function (Blueprint $table) {
            $table->string('middleName')->after('firstName')->nullable();
            $table->string('lastName')->after('middleName')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientFamilyMembers', function (Blueprint $table) {
            $table->dropColumn('middleName');
            $table->dropColumn('lastName');
        });
    }
}
