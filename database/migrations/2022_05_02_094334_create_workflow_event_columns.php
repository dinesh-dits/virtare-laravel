<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowEventColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('workflowEventsColumns', function (Blueprint $table) {
            $table->bigIncrements('workflowEventsColumnId');
            $table->string('udid');
            $table->bigInteger('providerId')->unsigned()->default(1);
            $table->bigInteger('workflowEventId')->unsigned()->default(null);
            $table->string('tableName');
            $table->string('columnName');
            $table->string('displayName');
            $table->string('dataType');
            $table->bigInteger('globalCodeCategoryId')->unsigned()->nullable();
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
        Schema::dropIfExists('workflowEventsColumns');
    }
}