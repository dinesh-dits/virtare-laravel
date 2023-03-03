<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEscalationTypeIdIntoEscalationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escalations', function (Blueprint $table) {
            $table->bigInteger('dueBy')->after('udid')->nullable();
            $table->bigInteger('typeId')->after('dueBy')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escalations', function (Blueprint $table) {
            $table->dropColumn('escalationTypeId');
        });
    }
}
