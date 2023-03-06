<?php

namespace App\Transformers\People;

use App\Models\Client\CareTeam;
use League\Fractal\TransformerAbstract;
use App\Models\Client\CareTeamMember;
use App\Models\Client\Site\Site;
use App\Models\User\User;

class PeopleTransformer extends TransformerAbstract
{


    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }
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
     * 
     * @return array
     */
    public function getTeam($userId)
    {
        $listData = [];
        $careTeamDetail = CareTeamMember::where(['contactId' => $userId])->get('careTeamId');
        if (isset($careTeamDetail) && !empty($careTeamDetail)) {
            $careTeam = CareTeam::whereIn('udid', $careTeamDetail)->get();
            foreach ($careTeam as $key => $care) {
                $listData[$key] = $care->name;
            }
            return implode(', ', $listData);
        } else {
            return '';
        }
    }
    public function getLocation($userId)
    {
        $listData = [];
        $careTeamDetail = CareTeamMember::where(['contactId' => $userId])->get('careTeamId');
        if (isset($careTeamDetail) && !empty($careTeamDetail)) {
            $careTeam = CareTeam::whereIn('udid', $careTeamDetail)->get('siteId');
            $site = Site::whereIn('udid', $careTeam)->get();
            foreach ($site as $key => $siteData) {
                $listData[$key] = $siteData->friendlyName;
            }
            return implode(', ', $listData);
        } else {
            return '';
        }
    }
    public function transform($data): array
    {
        if (!empty($data->lastName)) {
            $fullName = str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName));
        } else {
            $fullName = str_replace("  ", " ", ucfirst($data->firstName) . ' ' . ucfirst($data->middleName));
        }
        return [
            'id' => $data->udid,
            'userId' => $data->userId,
            'fullName' => $fullName,
            'firstName' => ucfirst($data->firstName),
            'middleName' => ucfirst($data->middleName),
            'lastName' => $data->lastName ? ucfirst($data->lastName) : '',
            'title' => $data->title ? ucfirst($data->title) : '',
            'timeZoneId' => (!empty($data->user)) ? (!empty($data->user->timeZone)) ? @$data->user->timeZone->udid : '' : @$data->timeZone->udid,
            'phoneNumber' => substr($data->phoneNumber, 0, 3) . '-' . substr($data->phoneNumber, 3, 3) . '-' . substr($data->phoneNumber, 6),
            'statusId' => 427,
            'status' => 'Active',
            'color' => '#7be800',
            'specializationId' => (!empty($data->specialization)) ? $data->specialization->id : '',
            'specialization' => (!empty($data->specialization)) ? $data->specialization->name : '',
            'site' => $this->getLocation($data->userId),
            'careTeam' => $this->getTeam($data->userId),
            'email' => (!empty($data->user))  ? $data->user->email : $data->email,
            'roleId' => (!empty($data->user->roles))  ? @$data->user->roles->udid   : @$data->role->udid,
            'role' => (!empty($data->user->roles))  ? @$data->user->roles->roles  : @$data->role->roles,
        ];
    }
}
