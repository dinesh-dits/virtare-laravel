<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddActionInActionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       // Schema::table('actions', function (Blueprint $table) {
            DB::table('actions')->insert(
                    array(
                        [
                            'screenId' => '1',
                            'name' => 'List Roles',
                        ],
                        [
                            'screenId' => '2',
                            'name' => 'List Global Codes',
                        ],
                        [
                            'screenId' => '3',
                            'name' => 'List CPT Codes',
                        ],
                        [
                            'screenId' => '4',
                            'name' => 'List Programs',
                        ],
                        [
                            'screenId' => '5',
                            'name' => 'List Providers',
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
        Schema::table('actions', function (Blueprint $table) {
            //
        });
    }
}
