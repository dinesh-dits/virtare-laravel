<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColorIntoGlobalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('globalCodes', function (Blueprint $table) {
            $table->string('color')->nullable()->after('name');
        });

        DB::table('globalCodes')->where([['globalCodeCategoryId', 74],['name','Trending']])->update([
            'color' => '#79a57c'
        ]);
        DB::table('globalCodes')->where([['globalCodeCategoryId', 74],['name','Urgent']])->update([
            'color' => '#E73149'
        ]);
        DB::table('globalCodes')->where([['globalCodeCategoryId', 74],['name','CCM']])->update([
            'color' => '#ef6c13'
        ]);
        DB::table('globalCodes')->where([['globalCodeCategoryId', 74],['name','Behavioral']])->update([
            'color' => '#f5a612'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('globalCodes', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
}
