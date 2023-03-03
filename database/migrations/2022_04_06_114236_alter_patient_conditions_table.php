<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPatientConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientConditions', function (Blueprint $table) {
            $table->bigInteger('providerId')->unsigned()->default(1)->after('id');
            $table->foreign('providerId')->references('id')->on('providers')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('providerLocationId')->unsigned()->default(1)->after('id');
            $table->foreign('providerLocationId')->references('id')->on('providerLocations')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
