<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddActionFieldCustomForm extends Migration
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
                    'globalCodeCategoryId' => '56',
                    'name' => 'DropDown',
                    'description' => 'Data Type',
                ]
            )
        );
       $dataType = DB::getPdo()->lastInsertId();
       $action = DB::table('globalCodes')->where('globalCodeCategoryId',55)->where('name','Custom Form')->first();
       DB::table('workFlowActionFields')->insert(
            array(
                [
                    'udid' => Str::uuid()->toString(),
                    'workFlowActionId' => $action->id,
                    'dataTypeId' => $dataType,
                    'fieldName' => 'Custom Form',
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
