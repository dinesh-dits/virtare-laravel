<?php

namespace App\Transformers\CPTCode;

use League\Fractal\TransformerAbstract;


class CptCodeActivityTransformer extends TransformerAbstract
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
            'id' => $data->udid,
            'name' =>  $data->cptCode->name.'-'.$data->name,
            'name1' => $data->name,
            'minimumUnit' => $data->minimumUnit,
            'billingInterval' => $data->billingInterval,
            'billingIntervalType' => $data->billingIntervalType,
            'isActive' => $data->isActive ? True : False,
            'cptCode' => $data->cptCode ? fractal()->item($data->cptCode)->transformWith(new CPTCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
        ];
    }
}
