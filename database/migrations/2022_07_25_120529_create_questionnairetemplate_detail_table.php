<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionnairetemplateDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionnaireFields', function (Blueprint $table) {
            $table->bigIncrements('questionnaireFieldId');
            $table->string('udid');
            $table->string('parameterKey')->nullable();
            $table->string('parameterValue')->nullable();
            $table->string('entityType')->nullable();
            $table->bigInteger('referenceId')->nullable();
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
        Schema::dropIfExists('questionnairetemplate_detail');
    }
}