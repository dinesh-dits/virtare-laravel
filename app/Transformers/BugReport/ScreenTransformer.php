<?php

namespace App\Transformers\BugReport;

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
            'isActive' => $data->isActive,
        ];
    }
}
