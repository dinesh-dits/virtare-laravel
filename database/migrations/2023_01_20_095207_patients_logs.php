<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PatientsLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patientLogs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('recordId')->unsigned();
            $table->string('ip_address'); 
            $table->text('previousData');    
            $table->text('newData');    
            $table->timestamp('date'); 
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable()->useCurrentOnUpdate();
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
