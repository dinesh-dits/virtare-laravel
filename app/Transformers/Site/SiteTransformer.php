<?php

namespace App\Transformers\Site;

use App\Transformers\AssignProgram\AssignProgramTransformer;
use League\Fractal\TransformerAbstract;
use App\Models\Client\CareTeam;
use App\Models\Client\CareTeamMember;
use App\Models\Patient\PatientProvider;

class SiteTransformer extends TransformerAbstract
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
    public function getPatientsCount($siteId)
    {
        $count = 0;
        $careTeamDetail = CareTeam::where(['siteId' => $siteId])->get();
        $careteams = array();
        foreach ($careTeamDetail as $key => $team) {
            $careteams[$key] = $team->udid;
        }
        if (0 < count($careteams)) {
            $count = PatientProvider::whereIn('providerId', $careteams)->count();
        }
        return $count;
    }

    public function getSitesCount($siteId)
    {
        $careTeamDetail = CareTeam::where(['siteId' => $siteId])->count();
        return $careTeamDetail;
    }

    public function getStaffCount($siteId)
    {
        $careTeamDetail = CareTeam::where(['siteId' => $siteId])->get();
        $careteams = array();
        foreach ($careTeamDetail as $key => $team) {
            $careteams[$key] = $team->udid;
        }
        return CareTeamMember::whereIn('careTeamId', $careteams)->count();
    }

    public function transform($data)
    {
        return [
            'location' => $data->virtual,
            'udid' => $data->udid,
            'friendlyName' => $data->friendlyName,
            'status' => (!empty($data->status)) ? $data->status->id : '',
            'statusName' => (!empty($data->status)) ? $data->status->name : '',
            'addressLine1' => $data->addressLine1,
            'addressLine2' => $data->addressLine2,
            'city' => $data->city,
            'zipCode' => $data->zipCode,
            'state' => (!empty($data->state)) ? $data->state->id : '',
            'stateName' => (!empty($data->state)) ? $data->state->iso : '',
            'CareTeam' => $this->getSitesCount($data->udid),
            'staff' => $this->getStaffCount($data->udid),
            'patients' => $this->getPatientsCount($data->udid),
            'virtual' => $data->virtual,
            'isHead' => (isset($data->head->id)) ? ucfirst($data->head->title) . ', ' . ucfirst($data->head->firstName) . ' ' . ucfirst($data->head->lastName) : '',
            'programs' => fractal()->collection($data->assignProgram)->transformWith(new AssignProgramTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
        ];
    }
}
