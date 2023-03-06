<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeApprovalCategoryIntoGlobalCategories extends Migration
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
                    'id' => '85',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Time Approval Type',
                    'preDefined' => '1',
                ],
            )
        );
        DB::table('globalCodes')->insert(
            array(
                [
                    'id' => '403',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '85',
                    'name' => 'Patient Review',
                    'description' => 'Time Approval Type',
                ],
                [
                    'id' => '404',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '85',
                    'name' => 'Communication',
                    'description' => 'Time Approval Type',
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
