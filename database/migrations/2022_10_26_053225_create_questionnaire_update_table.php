<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionnaireUpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionUpdate', function (Blueprint $table) {
            $table->id();
            $table->string('udid');
            $table->bigInteger('providerId')->default('1')->unsigned();
            $table->bigInteger('providerLocationId')->default('1')->unsigned();
            $table->bigInteger('programId')->unsigned();
            $table->bigInteger('templateId')->unsigned();
            $table->bigInteger('questionId')->unsigned();
            $table->bigInteger('parentId')->default('0')->unsigned();
            $table->bigInteger('childId')->default('0')->unsigned();
            $table->longText('dataObj');
            $table->string('entityType');
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
        Schema::dropIfExists('questionnaire_update');
    }
}
