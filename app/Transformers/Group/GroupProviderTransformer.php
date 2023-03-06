<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;


class GroupProviderTransformer extends TransformerAbstract
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
            'groupProviderId' => $data->groupProviderId,
            'udid' => $data->udid,
            'name' => $data->name,
            'targetUdid' => $data->providerUdid,
        ];
    }
}
