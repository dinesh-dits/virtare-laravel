<?php

namespace App\Transformers\Screen;

use App\Transformers\Action\ActionTransformer;
use App\Transformers\Action\RoleActionTransformer;
use League\Fractal\TransformerAbstract;


class PermissionScreenTransformer extends TransformerAbstract
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
            'name' => $data->name,
            'moduleId' => $data->moduleId,
            'actions' => fractal()->collection($data->action)->transformWith(new RoleActionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
        ];
    }
}
