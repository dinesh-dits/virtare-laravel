<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToDashboardWidgetByRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashboardWidgetByRoles', function (Blueprint $table) {
            $table->string('rows',10)->after('id');
            $table->string('columns',10)->after('id');
            $table->string('title',30)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboard_widget_by_roles', function (Blueprint $table) {
            $table->dropColumn('rows');
            $table->dropColumn('columns');
            $table->dropColumn('title');

        });
    }
}
