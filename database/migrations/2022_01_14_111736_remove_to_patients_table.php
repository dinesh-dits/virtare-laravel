<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign('patients_otherLanguageId_foreign');
            $table->dropColumn('otherLanguageId');
            $table->dropForeign('patients_contactTypeId_foreign');
            $table->dropColumn('contactTypeId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->bigInteger('otherLanguageId')->unsigned();
            $table->foreign('otherLanguageId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('contactTypeId')->unsigned();
            $table->foreign('contactTypeId')->references('id')->on('globalCodes')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
