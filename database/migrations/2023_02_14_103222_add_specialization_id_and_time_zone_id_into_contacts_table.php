<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpecializationIdAndTimeZoneIdIntoContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->bigInteger('specializationId')->unsigned()->nullable()->after('lastName');
            $table->bigInteger('timeZoneId')->unsigned()->nullable()->after('specializationId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('timeZoneId');
            $table->dropColumn('specializationId');
        });
    }
}
