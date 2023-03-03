<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropRoleIdToUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userRoles', function (Blueprint $table) {
            // $table->dropForeign('userroles_roleid_foreign');
            $table->dropColumn('roleId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('userRoles', function (Blueprint $table) {
            //
        });
    }
}
