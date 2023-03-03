<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CustomTemplateTableData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::table('customTemplates')->insert(
            array(
                [
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Blank',
                    'templateType' => 'dashboard',
                    'templateIcon' => 'blank.jpg',
                    'order'=>1
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'name' => 'One Column',
                    'templateType' => 'dashboard',
                    'templateIcon' => 'dashboard-one-column.jpg',
                    'order'=>2
                ],
                [
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Two Column',
                    'templateType' => 'dashboard',
                    'templateIcon' => 'dashboard-two-column.jpg',
                    'order'=>3
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Three Column',
                    'templateType' => 'dashboard',
                    'templateIcon' => 'dashboard-three-column.jpg',
                    'order'=>4
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Blank',
                    'templateType' => 'report',
                    'templateIcon' => 'blank.jpg',
                    'order'=>1
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Left Filter',
                    'templateType' => 'report',
                    'templateIcon' => 'report-left-filter.jpg',
                    'order'=>2
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Right Filter',
                    'templateType' => 'report',
                    'templateIcon' => 'report-right-filter.jpg',
                    'order'=>3
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Top Filter',
                    'templateType' => 'report',
                    'templateIcon' => 'report-top-filter.jpg',
                    'order'=>4
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Blank',
                    'templateType' => 'form',
                    'templateIcon' => 'blank.jpg',
                    'order'=>1
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'One Column',
                    'templateType' => 'form',
                    'templateIcon' => 'form-one-column.jpg',
                    'order'=>2
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Two Column',
                    'templateType' => 'form',
                    'templateIcon' => 'form-two-column.jpg',
                    'order'=>3
                ],[
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Below Grid',
                    'templateType' => 'form',
                    'templateIcon' => 'form-with-grid.jpg',
                    'order'=>4
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
