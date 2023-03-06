<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providerLocations', function (Blueprint $table) {
            $table->id();
            $table->string('locationName',50);
            $table->bigInteger('providerId')->unsigned()->nullable();
            $table->foreign('providerId')->references('id')->on('providers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('zipCode',10);
            $table->biginteger('stateId')->unsigned();
            $table->foreign('stateId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->biginteger('cityId')->unsigned();
            $table->foreign('cityId')->references('id')->on('globalCodes')->onUpdate('cascade')->onDelete('cascade');
            $table->text('locationAddress');
            $table->string('phoneNumber',20);
            $table->string('email',50);
            $table->text('websiteUrl');
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);
            $table->bigInteger('createdBy')->unsigned()->nullable();
            $table->bigInteger('updatedBy')->unsigned()->nullable();
            $table->bigInteger('deletedBy')->unsigned()->nullable();
            $table->foreign('createdBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('updatedBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('deletedBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();

            $table->timestamp('deletedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_locations');
    }
}
