<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddBugReportModuleScreenActionTable extends Migration
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
                    'id' => '29',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Bug Report',
                    'description' => 'The bug report module is for report bug of project on jira'
                ]
        )
    );

        DB::table('screens')->insert(
            array(
                [
                    'id' => '67',
                    'udid' => Str::uuid()->toString(),
                    'moduleId' => '29',
                    'name' => 'Bug Report',
                    'type' => 'Web',
                ]
        )
    );
        DB::table('actions')->insert(
            array(
                [
                    'id' => '493',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '67',
                    'name' => 'Add Bug Report',
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
