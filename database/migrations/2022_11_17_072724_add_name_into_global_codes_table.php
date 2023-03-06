<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameIntoGlobalCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('globalCodes')->where('id', '148')->update(['name' => 'Behavioral']);
        DB::table('globalCodes')->where('id', '149')->update(['name' => 'Clinical']);
        DB::table('globalCodes')->insert(
            array(
                [
                    'id' => '405',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '27',
                    'name' => 'Enrollment',
                    'description' => 'Patient Time Logs Category',
                ],
                [
                    'id' => '406',
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '27',
                    'name' => 'Admin',
                    'description' => 'Patient Time Logs Category',
                ]
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
