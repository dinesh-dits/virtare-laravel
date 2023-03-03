<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPdfReportExportRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vitalPdfReportExportRequest', function (Blueprint $table) {
            $table->string('customTimezone')->default('Asia/Calcutta')->after('patientId');
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
            // $table->dropColumn('customTimezone');
        });
    }
}
