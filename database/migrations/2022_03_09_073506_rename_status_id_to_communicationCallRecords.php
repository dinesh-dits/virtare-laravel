<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameStatusIdToCommunicationCallRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('communicationCallRecords', function (Blueprint $table) {
            $table->renameColumn('statusId', 'callStatusId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('communicationCallRecords', function (Blueprint $table) {
            //
        });
    }
}
