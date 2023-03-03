<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->string('firstName');
            $table->string('middleName');
            $table->string('lastName');
            $table->boolean('isAdmin');
            $table->boolean('isSystemUser');
            $table->string('entityType');
            $table->bigInteger('referenceId')->unsigned();
            $table->bigInteger('genderId')->unsigned();
            $table->string('title');
            $table->boolean('isSiteHead');
            $table->string('email')->nullable();
            $table->string('addressLine1');
            $table->string('addressLine2');
            $table->bigInteger('stateId')->unsigned();
            $table->string('city');
            $table->integer('phoneNumber')->length(15);
            $table->string('zipCode');
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);
            $table->bigInteger('createdBy')->unsigned()->nullable();
            $table->bigInteger('updatedBy')->unsigned()->nullable();
            $table->bigInteger('deletedBy')->unsigned()->nullable();
            $table->timestamp('createdAt')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updatedAt')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('contacts');
    }
}
