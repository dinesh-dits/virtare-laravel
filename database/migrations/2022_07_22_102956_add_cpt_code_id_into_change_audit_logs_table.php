<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCptCodeIdIntoChangeAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('changeAuditLogs', function (Blueprint $table) {
            $table->bigInteger('cptCodeId')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('changeAuditLogs', function (Blueprint $table) {
            $table->dropColumn('cptCodeId');
        });
    }
}
