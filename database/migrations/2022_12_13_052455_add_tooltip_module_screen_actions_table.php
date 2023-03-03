<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddTooltipModuleScreenActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('modules')->insert(
            array(
                [
                    'id' => '28',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Tool Tip',
                    'description' => 'Tool tip module is decribe about information of forms labels',
                ]
        )
    );

        DB::table('screens')->insert(
            array(
                [
                    'id' => '66',
                    'udid' => Str::uuid()->toString(),
                    'moduleId' => '28',
                    'name' => 'Tool Tip',
                    'type' => 'Web',
                ]
        )
    );

        DB::table('actions')->insert(
            array(
                [
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '66',
                    'name' => 'Tool Tip Update',
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
