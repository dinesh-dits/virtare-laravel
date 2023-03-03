<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;


class GroupPermissionTransformer extends TransformerAbstract
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
            'id' =>$data->id,
            'name' =>$data->name,
            'screens' =>json_decode($data->screens,true),
		];
    }
}
