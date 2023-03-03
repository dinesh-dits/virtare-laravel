<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBugReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bugReports', function (Blueprint $table) {
            $table->bigIncrements('bugReportId');
            $table->string('udid');
            $table->bigInteger('userId');
            $table->bigInteger('screenId')->nullable();
            $table->string('subjectTitle')->nullable(); 
            $table->string('buildVersion')->nullable();
            $table->string('osVersion')->nullable();
            $table->string('deviceName')->nullable();
            $table->string('deviceId')->nullable();
            $table->string('userLoginEmail')->nullable();
            $table->string('reportBugEmail')->nullable();
            $table->string('Category')->nullable();
            $table->string('Platform')->nullable();
            $table->string('location')->nullable();
            $table->string('attachmentType')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('bugReports');
    }
}
