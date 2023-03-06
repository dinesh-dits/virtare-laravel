<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeviceTypeIdToGeneralParameterGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('generalParameterGroups', function (Blueprint $table) {
            $table->bigInteger('deviceTypeId')->unsigned()->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_parameter_groups', function (Blueprint $table) {
            $table->dropColumn('deviceTypeId');
        });
    }
}
