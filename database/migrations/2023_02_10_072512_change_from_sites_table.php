<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFromSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('addressLine1')->nullable()->change();
            $table->string('addressLine2')->nullable()->change();
            $table->bigInteger('stateId')->unsigned()->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('zipCode')->nullable()->change();
            $table->bigInteger('siteHead')->unsigned()->after('addressLine2');
            $table->boolean('virtual')->unsigned()->after('siteHead');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            //
        });
    }
}
