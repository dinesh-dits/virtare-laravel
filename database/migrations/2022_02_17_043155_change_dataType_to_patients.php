<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDataTypeToPatients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('contactTypeId')->change();
            $table->string('contactTimeId')->change();
            $table->string('otherLanguageId')->change();
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
            $table->bigInteger('contactTimeId')->unsigned();
            $table->bigInteger('contactTimeId')->unsigned();
            $table->bigInteger('otherLanguageId')->unsigned();
        });
    }
}
