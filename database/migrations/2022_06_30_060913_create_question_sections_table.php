<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionSections', function (Blueprint $table) {
            $table->bigIncrements('questionSectionId');
            $table->string('udid');
            $table->bigInteger('questionnaireSectionId');
            $table->bigInteger('questionId');
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
        // Schema::dropIfExists('questionSections');
    }
}