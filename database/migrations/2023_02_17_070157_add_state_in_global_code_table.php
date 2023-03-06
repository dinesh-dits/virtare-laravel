<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddStateInGlobalCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       // Schema::table('globalCodes', function (Blueprint $table) {
            DB::table('globalCodes')->insert(
                array(
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'American Samoa',
                        'iso' =>  'AS',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'District of Columbia',
                        'iso' =>  'DC',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Guam',
                        'iso' =>  'GU',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Indiana',
                        'iso' =>  'IN',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Iowa',
                        'iso' =>  'IA',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Kansas',
                        'iso' =>  'KS',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Kentucky',
                        'iso' =>  'KY',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Louisiana',
                        'iso' =>  'LA',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Maine',
                        'iso' =>  'ME',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Maryland',
                        'iso' => 'MD',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Massachusetts',
                        'iso' =>  'MA',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Michigan',
                        'iso' =>  'MI',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Minnesota',
                        'iso' =>  'MN',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Mississippi',
                        'iso' => 'MS',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Montana',
                        'iso' =>  'MT',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Nebraska',
                        'iso' =>  'NE',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Nevada',
                        'iso' =>  'NV',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'New Hampshire',
                        'iso' =>  'NH',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'North Carolina',
                        'iso' =>  'NC',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'North Dakota',
                        'iso' =>  'ND',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Northern Mariana Islands',
                        'iso' =>  'MP',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Ohio',
                        'iso' =>  'OH',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Oklahoma',
                        'iso' =>  'OK',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Oregon',
                        'iso' =>  'OR',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Pennsylvania',
                        'iso' =>  'PA',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Puerto Rico',
                        'iso' =>  'PR',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Rhode Island',
                        'iso' =>  'RI',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'South Carolina',
                        'iso' =>  'SC',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'South Dakota',
                        'iso' =>  'SD',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Tennessee',
                        'iso' =>  'TN',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Trust Territories',
                        'iso' =>  'TT',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Utah',
                        'iso' =>  'UT',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Virginia',
                        'iso' =>  'VA',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Virgin Islands',
                        'iso' =>  'VI',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'West Virginia',
                        'iso' =>  'WV',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Wisconsin',
                        'iso' =>  'WI',
                        'predefined' => 1,
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'globalCodeCategoryId' => 21,
                        'description' => 'States In USA',
                        'name' => 'Wyoming',
                        'iso' =>  'WY',
                        'predefined' => 1,
                    ],
            )
        );
        //});
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
