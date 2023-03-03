<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;


class GroupProgramTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];


    public function transform($data): array
    {
        return [
            'groupProgramId' => $data->groupProgramId,
            'udid' => $data->udid,
            'name' => $data->name,
            'targetUdid' => $data->programUdid,
            'programId' => (!empty($data->programId))?$data->programId:'',
        ];
    }
}
