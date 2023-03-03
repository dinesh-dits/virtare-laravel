<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveToProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign('programs_typeId_foreign');
            $table->dropColumn('typeId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->bigInteger('typeId')->unsigned();
            $table->foreign('typeId')->references('id')->on('programs')->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
