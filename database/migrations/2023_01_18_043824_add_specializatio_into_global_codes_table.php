<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecializatioIntoGlobalCodesTable extends Migration
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
                    'globalCodeCategoryId' => '2',
                    'name' => 'Administration',
                    'description' => 'Specialization Type',
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
