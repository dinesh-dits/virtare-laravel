<?php

namespace App\Transformers\CPTCode;

use League\Fractal\TransformerAbstract;


class ConditionTransformer extends TransformerAbstract
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
                'code' => $data->code,
                'description' => $data->description,
                'isActive'=> $data->isActive ? True : False
		];
    }
}
