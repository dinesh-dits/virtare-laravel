<?php

namespace App\Transformers\CPTCode;

use League\Fractal\TransformerAbstract;


class CPTCodeServiceConditionTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];


    public function transform($data): array
    {
        return $data->condition ? fractal()->item($data->condition)->transformWith(new ConditionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"";

        // return [
        //         'id' => $data->id,
        //         'isActive'=> $data->isActive ? True : False,
        //         'condition' => $data->condition ? fractal()->item($data->condition)->transformWith(new ConditionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",

        //     ];
    }
}
