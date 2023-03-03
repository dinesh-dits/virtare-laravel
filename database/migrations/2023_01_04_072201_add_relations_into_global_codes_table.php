<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationsIntoGlobalCodesTable extends Migration
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
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '8',
                    'name' => 'Partner',
                    'description' => 'Relationship',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '8',
                    'name' => 'Husband',
                    'description' => 'Relationship',
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'globalCodeCategoryId' => '8',
                    'name' => 'Wife',
                    'description' => 'Relationship',
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
