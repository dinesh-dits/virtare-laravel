<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('moduleId')->after('city');
            $table->string('tagId')->after('city');
            $table->string('phoneNumber')->after('city');
            $table->string('zipcode')->after('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('modules');
            $table->dropColumn('tagId');
            $table->dropColumn('phoneNumber');
            $table->dropColumn('zipcode');
        });
    }
}
