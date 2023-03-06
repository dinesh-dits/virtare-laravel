<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterExcelReportExportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exportReportRequest', function (Blueprint $table) {
            $table->string('customTimezone')->default('Asia/Calcutta')->after('reportType');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            Schema::table('exportReportRequest', function (Blueprint $table) {
                $table->dropColumn('customTimezone');
            });
    }
}
