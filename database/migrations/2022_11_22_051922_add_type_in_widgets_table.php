<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeInWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->bigInteger('type')->after('widgetModuleId')->nullable();
            $table->string('endPoint')->after('type')->nullable();
            $table->string('widgetType')->after('endPoint')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('endPoint');
            $table->dropColumn('widgetType');
        });
    }
}
