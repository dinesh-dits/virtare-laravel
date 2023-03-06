<?php

namespace App\Transformers\BitrixField;

use League\Fractal\TransformerAbstract;


class BitrixFieldTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->id,
            'bitrixId' => $data->bitrixId,
            'patientId' => $data->patientId,
            'isActive'  => $data->isActive ? True : False
        ];
    }
}
