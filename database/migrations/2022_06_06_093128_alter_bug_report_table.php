<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBugReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bugReports', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->bigInteger('categoryId')->nullable()->after('bugReportEmail');
            
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
            $table->string('category')->after('bugReportEmail');
            $table->dropColumn('categoryId');
            
        });
    }
}
