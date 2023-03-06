<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCategoryInBugReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bugReports', function (Blueprint $table) {
            $table->renameColumn('Category', 'category');
            $table->renameColumn('Platform', 'platform');
            $table->renameColumn('reportBugEmail', 'bugReportEmail');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bugReports', function (Blueprint $table) {
            $table->renameColumn('category', 'Category');
            $table->renameColumn('platform', 'Platform');
            $table->renameColumn('bugReportEmail', 'reportBugEmail');
        });
    }
}
