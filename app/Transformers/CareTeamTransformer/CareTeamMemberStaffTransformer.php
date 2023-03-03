<?php

namespace App\Transformers\CareTeamTransformer;

use App\Helper;
use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;
use App\Transformers\AssignProgram\AssignProgramTransformer;

class CareTeamMemberStaffTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        $uuid = Helper::tableNameIdToUdid('App\Models\Role\Role', @$data->user->roleId);
        $timeZone = (!empty($data->user->timeZoneId)) ? Timezone::where('id', @$data->user->timeZoneId)->first() : '';
        return [
            'udid' => @$data->udid,
            'title' => @$data->title,
            'firstName' => ucfirst($data->firstName),
            'middleName' => ucfirst($data->middleName),
            'lastName' => ucfirst($data->lastName),
            'name' => ucfirst($data->lastName) . ', ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName),
            'email' => @$data->user->email,
            'phoneNumber' => @$data->phoneNumber,
            'roleId' => @$uuid,
            'roleName' => @$data->user->roles->roles,
            'timeZoneId' => @$timeZone->udid,
        ];
    }
}
