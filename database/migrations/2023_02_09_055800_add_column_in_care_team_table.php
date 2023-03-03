<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInCareTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_teams', function (Blueprint $table) {
            $table->string('clientId', 10)->after('siteId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('care_teams', 'clientId')) {
            Schema::table('care_teams', function (Blueprint $table) {
                $table->dropColumn('clientId');
            });
        }
    }
}
