<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsToPatientFamilyMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientFamilyMembers', function (Blueprint $table) {
            $table->string('fullName',30)->nullable()->change();
            $table->string('phoneNumber',20)->nullable()->change();
            $table->bigInteger('contactTypeId')->nullable()->unsigned()->change();
            $table->bigInteger('contactTimeId')->nullable()->unsigned()->change();
            $table->bigInteger('genderId')->nullable()->unsigned()->change();
            $table->bigInteger('relationId')->nullable()->unsigned()->change();
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
            //
        });
    }
}
