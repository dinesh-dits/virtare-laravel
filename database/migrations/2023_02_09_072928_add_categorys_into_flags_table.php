<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategorysIntoFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('flags')->where('id', 6)->update([
            'category' => '2'
        ]);
        DB::table('flags')->where('id', 7)->update([
            'category' => '1'
        ]);
        DB::table('flags')->where('id', 8)->update([
            'category' => '1'
        ]);
        DB::table('flags')->where('id', 9)->update([
            'category' => '1'
        ]);
        DB::table('flags')->where('id', 10)->update([
            'category' => '2'
        ]);
        DB::table('flags')->where('id', 11)->update([
            'category' => '2'
        ]);
        DB::table('flags')->where('id', 12)->update([
            'category' => '2'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flags', function (Blueprint $table) {
            //
        });
    }
}
