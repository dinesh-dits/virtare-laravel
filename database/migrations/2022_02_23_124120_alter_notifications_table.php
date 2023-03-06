<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_notificationtypeid_foreign');
            $table->dropColumn('notificationTypeId');
            $table->string('entity')->after('isRead');
            $table->bigInteger('referenceId')->unsigned()->after('isRead');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('entity');
            $table->dropColumn('referenceId');
        });
    }
}
