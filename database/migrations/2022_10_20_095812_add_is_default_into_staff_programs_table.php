<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDefaultIntoStaffProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staffPrograms', function (Blueprint $table) {
            $table->bigInteger('isDefault')->after('locationId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staffPrograms', function (Blueprint $table) {
            $table->dropColumn('isDefault');
        });
    }
}
