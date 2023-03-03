<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddCustomFieldGlobalCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('globalCodes')->insert(
            array(
                [
                    'id' => '426',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '46',
                    'name' => 'Custom',
                    'description' => 'custom'                    
                ],
            )
        );

        DB::table('globalStartEndDate')->insert(
            array(
                [
                    'id' => '6',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeId' => '426',
                    'intervalType' => 'custom',
                    'number' => 0                    
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
