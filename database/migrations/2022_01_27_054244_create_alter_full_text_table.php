<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateAlterFullTextTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            DB::statement('ALTER TABLE staffs ADD FULLTEXT firstName(firstName)');
            DB::statement('ALTER TABLE patients ADD FULLTEXT firstName(firstName)');
            DB::statement('ALTER TABLE globalCodes ADD FULLTEXT name(name)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::drop('ALTER TABLE staffs ADD FULLTEXT firstName(firstName)');
        DB::drop('ALTER TABLE patients ADD FULLTEXT firstName(firstName)');
        DB::drop('ALTER TABLE globalCodes ADD FULLTEXT name(name)');
    }
}
