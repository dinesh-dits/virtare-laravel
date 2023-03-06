<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCareTeamToPatientSatffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patientStaffs', function (Blueprint $table) {
            $table->boolean('isCareTeam')->after('isPrimary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patientStaffs', function (Blueprint $table) {
            $table->dropColumn('isCareTeam');
        });
    }
}
