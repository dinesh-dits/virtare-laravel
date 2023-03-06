<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValueSqlEventColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflowEventsColumns', function($table) {
            $table->longText('globalCodeCategoryId')->change();
        });
        Schema::table('workflowEventsColumns', function($table) {
            $table->renameColumn('globalCodeCategoryId', 'valueSql');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflowEventsColumns', function($table) {
            $table->bigInteger('valueSql')->change();;
        });
        Schema::table('workflowEventsColumns', function($table) {
            $table->renameColumn('valueSql', 'globalCodeCategoryId');
        });
    }
}
