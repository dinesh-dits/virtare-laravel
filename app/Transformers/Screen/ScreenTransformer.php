<?php

namespace App\Transformers\Screen;

use App\Transformers\Action\ActionTransformer;
use League\Fractal\TransformerAbstract;


class ScreenTransformer extends TransformerAbstract
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
            'id' => $data->id,
            'name' => $data->name,
            'moduleId' => $data->moduleId,
            'actions' => fractal()->collection($data->action)->transformWith(new ActionTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
        ];
    }
}
