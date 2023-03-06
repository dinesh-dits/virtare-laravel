<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientTypeIntoGlobalCodesTable extends Migration
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
                    'id' => '89',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Contract Type',
                    'preDefined' => '1',
                ],
            )
        );
        DB::table('globalCodes')->insert(
            array(
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '89',
                    'name' => 'MSO',
                    'description' => 'Contract Type',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '89',
                    'name' => 'Type1',
                    'description' => 'Contract Type',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '89',
                    'name' => 'Type2',
                    'description' => 'Contract Type',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '89',
                    'name' => 'Type3',
                    'description' => 'Contract Type',
                    'preDefined' => '1',
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
        Schema::table('globalCodes', function (Blueprint $table) {
            //
        });
    }
}
