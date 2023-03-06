<?php

namespace App\Transformers\CPTCode;

use League\Fractal\TransformerAbstract;


class ServiceTransformer extends TransformerAbstract
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
                'udid' => $data->udid,
                'name' => $data->name,
                'isActive'=> $data->isActive ? True : False
		];
    }
}
