<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProviderStatusIntoGlobalCodesTable extends Migration
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
                    'id' => '88',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Client Status',
                    'preDefined' => '1',
                ],
            )
        );
        DB::table('globalCodes')->insert(
            array(
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '88',
                    'name' => 'Active',
                    'description' => 'Client Status',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '88',
                    'name' => 'In Active',
                    'description' => 'Client Status',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '88',
                    'name' => 'Suspended',
                    'description' => 'Client Status',
                    'preDefined' => '1',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '88',
                    'name' => 'Deleted',
                    'description' => 'Client Status',
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
