<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;

class InventorySerialTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
            'id' => $data->udid,
            'name' => ($data->serialNumber != NULL) ? $data->serialNumber : $data->macAddress,
        ];
    }
}
