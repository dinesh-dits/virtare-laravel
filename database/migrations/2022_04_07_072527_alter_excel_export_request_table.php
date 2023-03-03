<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterExcelExportRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vitalPdfReportExportRequest', function (Blueprint $table) {
            $table->string('fromDate')->change();
            $table->string('toDate')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vitalPdfReportExportRequest', function (Blueprint $table) {
            $table->dropColumn('fromDate');
            $table->dropColumn('toDate');
        });
    }
}
