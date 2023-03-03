<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddClientActionsInActionsTable extends Migration
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
                    'id' => 33,
                    'udid' => Str::uuid()->toString(),
                    'name' => 'Clients',
                    'description' => 'This module is to add client site and people',
                ],
            )
        );  
        
        DB::table('screens')->insert(
            array(
                [
                    'id' => 72,
                    'udid' => Str::uuid()->toString(),
                    'moduleId' => 33,
                    'name' => 'Client List',
                    'type' => 'Web',
                ],
            )
        );

        DB::table('screens')->insert(
            array(
                [
                    'id' => 73,
                    'udid' => Str::uuid()->toString(),
                    'moduleId' => 33,
                    'name' => 'Client Detail',
                    'type' => 'Web',
                ],
            )
        );

            DB::table('actions')->insert(
                array(
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 72,
                        'name' => 'Add New Client',
                        'function' => 'addClient',
                        'controller' =>'ClientController ',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 72,
                        'name' => 'View Client',
                        'function' => 'listClient',
                        'controller' =>'ClientController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Info Edit',
                        'function' => 'listClient',
                        'controller' =>'ClientController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Info Update',
                        'function' => 'updateClient',
                        'controller' =>'ClientController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Info Suspend',
                        'function' => 'updateClient',
                        'controller' =>'ClientController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Info List',
                        'function' => 'listClient',
                        'controller' =>'ClientController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'People List ',
                        'function' => 'listPeople',
                        'controller' =>'PeopleController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Add New People',
                        'function' => 'createPeople',
                        'controller' =>'PeopleController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Edit New People',
                        'function' => 'detailPeople',
                        'controller' =>'PeopleController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Update New People',
                        'function' => 'updatePeople',
                        'controller' =>'PeopleController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Site List',
                        'function' => 'listSite',
                        'controller' =>'SiteController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Add New Site',
                        'function' => 'addSite',
                        'controller' =>'SiteController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Edit New Site',
                        'function' => 'listSite',
                        'controller' =>'SiteController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Update Site',
                        'function' => 'updateSite',
                        'controller' =>'SiteController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Care Team List',
                        'function' => 'listCareTeam',
                        'controller' =>'CareTeamController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Add New Care Team',
                        'function' => 'createCareTeam',
                        'controller' =>'CareTeamController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Edit Care Team',
                        'function' => 'listCareTeam',
                        'controller' =>'CareTeamController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Update Care Team',
                        'function' => 'updateCareTeam',
                        'controller' =>'CareTeamController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Add New Member',
                        'function' => 'addMember',
                        'controller' =>'CareTeamMemberController',
                    ],
                    [
                        'udid' => Str::uuid()->toString(),
                        'screenId' => 73,
                        'name' => 'Patient List',
                        'function' => 'get_patients',
                        'controller' =>'ClientController',
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
        // Schema::table('actions', function (Blueprint $table) {
        //     //
        // });
    }
}
