<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClientQuestionniareTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clientQuestionnaireTemplates', function (Blueprint $table) {
            $table->string('status')->nullable()->after('entityType');
            $table->bigInteger('percentage')->default('0')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clientQuestionnaireTemplates', function (Blueprint $table) {
            $table->string('status')->nullable()->after('entityType');
            $table->bigInteger('percentage')->default('0')->after('status');
        });
    }
}
