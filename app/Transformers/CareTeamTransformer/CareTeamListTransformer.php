<?php

namespace App\Transformers\CareTeamTransformer;

use League\Fractal\TransformerAbstract;
use App\Transformers\AssignProgram\AssignProgramTransformer;
use App\Models\Client\CareTeam;
use App\Models\Patient\PatientProvider;
use App\Models\Staff\Staff;

class CareTeamListTransformer extends TransformerAbstract
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
    public function getSiteName($CId)
    {
        $site = CareTeam::with('site')->where('id', $CId)->first();
        return $site->site->friendlyName;
    }

    public function getPatients($CId)
    {
        $count = PatientProvider::where('providerId', $CId)->count();
        return $count;

    }

    public function siteHead($CId)
    {
        $count = Staff::where('userId', $CId)->first();
        return $count->title . ',' . $count->firstName . ' ' . $count->lastName;

    }

    public function transform($data)
    {
        return [
            'udid' => @$data->udid,
            'name' => @$data->name,
            // 'site' => $this->getSiteName($data->id),
            'patients' => $this->getPatients($data->udid),
            'teamHeadId' => @$data->headName(@$data->head->contactId)->teamHeadId,
            'teamHead' => ((isset($data->head->contactId)) ? $this->siteHead($data->head->contactId) : ''),
            'staff' => @$data->careTeamMember->count(),
            'programs' => fractal()->collection($data->assignProgram)->transformWith(new AssignProgramTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
            'care_team_members' => fractal()->collection($data->careTeamMember)->transformWith(new CareTeamMemberTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
        ];
    }
}
