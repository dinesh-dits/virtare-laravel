<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddTypeInGlobalCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('globalCodeCategories')->insert(
            array(
                [
                    'id' => '86',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Widget Type',
                    'preDefined' => '1',
                ],
            )
        );
        DB::table('globalCodes')->insert(
            array(
                [
                    'id' => '407',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '86',
                    'name' => 'Graph',
                    'description' => 'Widget Type',
                ],
                [
                    'id' => '408',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '86',
                    'name' => 'Table',
                    'description' => 'Widget Type',
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
