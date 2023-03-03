<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoProviderLocationProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providerLocationPrograms', function (Blueprint $table) {
            $table->string('entityType')->nullable()->after('udid');
            $table->bigInteger('referenceId')->unsigned()->nullable()->after('entityType');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providerLocationPrograms', function (Blueprint $table) {
            $table->dropColumn('entityType');
            $table->dropColumn('referenceId');
        });
    }
}
