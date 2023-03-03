<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameClientResponseProgramScoreIdFromClientResponseProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clientResponsePrograms', function (Blueprint $table) {
            $table->renameColumn('clientResponseProgramScoreId','clientResponseProgramId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clientResponsePrograms', function (Blueprint $table) {
            //
        });
    }
}
