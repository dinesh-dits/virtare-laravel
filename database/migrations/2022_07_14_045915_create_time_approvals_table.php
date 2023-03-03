<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timeApprovals', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->bigInteger('staffId')->unsigned()->nullable();
            $table->bigInteger('patientId')->unsigned()->nullable();
            $table->integer('time')->nullable();
            $table->bigInteger('typeId')->unsigned()->nullable();
            $table->bigInteger('statusId')->unsigned()->nullable();
            $table->string('entityType')->nullable();
            $table->bigInteger('referenceId')->unsigned()->nullable();
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
        Schema::dropIfExists('time_approvals');
    }
}
