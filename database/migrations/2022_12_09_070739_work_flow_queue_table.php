<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WorkFlowQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workFlowQueue', function (Blueprint $table) {
            $table->bigIncrements('workFlowQueueId');
            $table->string('udid');
            $table->string('entityType')->default('Country');            
            $table->bigInteger('programId')->unsigned()->default(5);
            $table->bigInteger('providerId')->unsigned()->default(1);
            $table->bigInteger('providerLocationId')->unsigned()->default(1);
            $table->bigInteger('workFlowId');
            $table->bigInteger('keyId');
            $table->boolean('status');
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);
            $table->bigInteger('createdBy')->unsigned()->nullable();
            $table->bigInteger('updatedBy')->unsigned()->nullable();
            $table->bigInteger('deletedBy')->unsigned()->nullable();        
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable()->useCurrentOnUpdate();
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
        Schema::dropIfExists('workFlowQueue');
    }
}
