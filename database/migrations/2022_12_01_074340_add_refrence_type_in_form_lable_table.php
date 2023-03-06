<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefrenceTypeInFormLableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formLables', function (Blueprint $table) {
            $table->string('refrenceType')->after('type')->nullable();
            $table->renameColumn('formId', 'refrenceId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formLables', function (Blueprint $table) {
            $table->dropColumn('refrenceType');
            $table->renameColumn('refrenceId', 'formId');
        });
    }
}
