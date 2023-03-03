<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title',30);
            $table->text('description');
            $table->date('startDate');
            $table->date('dueDate');
            $table->text('taskCategoryId');
            $table->bigInteger('taskTypeId')->unsigned();
            $table->foreign('taskTypeId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('priorityId')->unsigned();
            $table->foreign('priorityId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('taskStatusId')->unsigned();
            $table->foreign('taskStatusId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
            $table->text('assignedTo');
            $table->boolean('isActive')->default(1);
            $table->boolean('isDelete')->default(0);
            $table->bigInteger('createdBy')->unsigned()->nullable();
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('updatedBy')->unsigned()->nullable();
            $table->foreign('updatedBy')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('deletedBy')->unsigned()->nullable();
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('tasks');
    }
}
