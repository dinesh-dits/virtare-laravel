<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPatientGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientGoals', function (Blueprint $table) {
            // $table->dropColumn('vitalTypeId');
            $table->dropColumn('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientGoals',function(Blueprint $table){
            $table->bigInteger('vitalTypeId')->unsigned();
            $table->string('value',10);
        });
    }
}
