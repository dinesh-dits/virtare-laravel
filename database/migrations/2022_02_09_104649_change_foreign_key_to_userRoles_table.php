<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeForeignKeyToUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('userRoles', function (Blueprint $table) {
            $table->dropForeign('userroles_userid_foreign');
            $table->dropColumn('userId');
            $table->bigInteger('accessRoleId')->unsigned()->nullable()->after('udid');
            $table->foreign('accessRoleId')->references('id')->on('accessRoles')->onUpdate('cascade')->onDelete('cascade');
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
