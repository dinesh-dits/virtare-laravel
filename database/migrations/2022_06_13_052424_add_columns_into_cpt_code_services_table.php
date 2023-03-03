<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoCptCodeServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cptCodeServices', function (Blueprint $table) {
            $table->bigInteger('referenceId')->unsigned()->after('entity')->nullable();
            $table->string('units')->after('referenceId')->nullable();
            $table->decimal('cost')->after('units')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cptCodeServices', function (Blueprint $table) {
            $table->dropColumn('referenceId');
            $table->dropColumn('units');
            $table->dropColumn('cost');
        });
    }
}
