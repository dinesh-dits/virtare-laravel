<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class AddWorkFlowModuleTable extends Migration
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
                    'id' => '30',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'WorkFlow',
                    'description' => 'The WorkFlow module is for add WorkFlow of project'
                ],
                [
                    'id' => '31',
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Custom Form',
                    'description' => 'The Custom Form module is for add Custom Forms'
                ]
        )
    );

        DB::table('screens')->insert(
            array(
                [
                    'id' => '68',
                    'udid' => Str::uuid()->toString(),
                    'moduleId' => '30',
                    'name' => 'WorkFlow List',
                    'type' => 'Web',
                ],
                [
                    'id' => '69',
                    'udid' => Str::uuid()->toString(),
                    'moduleId' => '31',
                    'name' => 'Custom Form',
                    'type' => 'Web',
                ],
                [
                    'id' => '70',
                    'udid' => Str::uuid()->toString(),
                    'moduleId' => '31',
                    'name' => 'Form Builder',
                    'type' => 'Web',
                ]
        )
    );
        DB::table('actions')->insert(
            array(
                [
                    'id' => '494',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '68',
                    'name' => 'Add WorkFlow',
                ],
                [
                    'id' => '495',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '68',
                    'name' => 'Configuration',
                ],
                [
                    'id' => '496',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '68',
                    'name' => 'Actions',
                ],
                [
                    'id' => '497',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '68',
                    'name' => 'Edit WorkFlow',
                ],
                [
                    'id' => '498',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '69',
                    'name' => 'Add Custom Form',
                ],
                [
                    'id' => '499',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '69',
                    'name' => 'Delete Custom Form',
                ],
                [
                    'id' => '500',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '69',
                    'name' => 'Assign Custom Form',
                ],
                [
                    'id' => '501',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '69',
                    'name' => 'Information Custom Form',
                ],
                [
                    'id' => '502',
                    'udid' => Str::uuid()->toString(),
                    'screenId' => '70',
                    'name' => 'Add Form Builder',
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
