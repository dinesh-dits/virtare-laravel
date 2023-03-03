<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('firstName',25)->nullable()->change();
            $table->date('dob')->nullable()->change();
            $table->bigInteger('genderId')->nullable()->unsigned()->change();
            $table->bigInteger('languageId')->nullable()->unsigned()->change();
            $table->bigInteger('otherLanguageId')->nullable()->unsigned()->change();
            $table->bigInteger('userId')->unsigned()->change();
            $table->string('phoneNumber',20)->nullable()->change();
            $table->bigInteger('contactTypeId')->nullable()->unsigned()->change();
            $table->bigInteger('contactTimeId')->nullable()->unsigned()->change();
            $table->string('medicalRecordNumber',30)->nullable()->change();
            $table->biginteger('countryId')->nullable()->unsigned()->change();
            $table->biginteger('stateId')->nullable()->unsigned()->change();
            $table->string('city',50)->nullable()->change();
            $table->string('zipCode',10)->nullable()->change();
            $table->string('appartment',20)->nullable()->change();
            $table->string('address',200)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            //
        });
    }
}
