<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToChangeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('changeLogs', function (Blueprint $table) {
            $table->bigInteger('deletedBy')->unsigned()->nullable()->after('isDeleted');
            $table->bigInteger('updatedBy')->unsigned()->nullable()->after('isDeleted');
            $table->bigInteger('createdBy')->unsigned()->nullable()->after('isDeleted');
            $table->foreign('createdBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('updatedBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('deletedBy')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('change_logs', function (Blueprint $table) {
            //
        });
    }
}
