<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToPatientGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientGoals', function (Blueprint $table) {
           $table->bigInteger('vitalFieldId')->unsigned()->after('id');
           $table->foreign('vitalFieldId')->references('id')->on('vitalFields')->onUpdate('cascade')->onDelete('cascade');
            $table->text('highValue')->after('id');
            $table->text('lowValue')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientGoals', function (Blueprint $table) {
           $table->dropForeign('patientgoals_vitalfiledid_foreign');
           $table->dropColumn('vitalFieldId');
           $table->dropColumn('highValue');
           $table->dropColumn('lowValue');
        });
    }
}
