<?php

namespace App\Transformers\CareTeamTransformer;

use App\Helper;
use League\Fractal\TransformerAbstract;
use App\Transformers\AssignProgram\AssignProgramTransformer;
use App\Models\Client\AssignProgram\AssignProgram;
use App\Models\Program\Program;

class CareTeamTransformer extends TransformerAbstract
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
        $listData = [];
        $peoples = AssignProgram::where(['referenceId' => $data->udid, 'entityType' => 'CareTeam'])->get('programId');
        $program = Program::whereIn('id', $peoples)->get();
        foreach ($program as $key => $people) {
            $listData[$key] = $people->udid;
        }

        return [
            'udid' => @$data->udid,
            'name' => @$data->name,
            'siteId' => @$data->site->udid,
            'siteName' => ucfirst(@$data->site->friendlyName),
            'teamHeadId' => @$data->headName(@$data->head->contactId)->teamHeadId,
            'teamHeadName' => @$data->headName(@$data->head->contactId)->name,
            'programs' => (!empty($listData)) ? $listData : [],
            //'programs' => fractal()->collection($data->assignProgram)->transformWith(new AssignProgramTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
            'care_team_members' => fractal()->collection($data->careTeamMember)->transformWith(new CareTeamMemberTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
        ];
    }
}
