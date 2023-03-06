<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriorityGlobalCodesTable extends Migration
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
                    'id' => '87',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Priority',
                    'preDefined' => '1',
                ],
            )
        );
        DB::table('globalCodes')->insert(
            array(
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '87',
                    'name' => 'Standard',
                    'description' => 'priority',
                    'priority' => '1',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '87',
                    'name' => 'High',
                    'description' => 'priority',
                    'priority' => '3',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '87',
                    'name' => 'Medium',
                    'description' => 'priority',
                    'priority' => '2',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '87',
                    'name' => 'Other',
                    'description' => 'priority',
                    'priority' => '4',
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
