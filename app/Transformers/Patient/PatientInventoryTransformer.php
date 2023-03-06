<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\Inventory\InventoryTransformer;

class PatientInventoryTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        if(empty($data)){
            return [];
        }
        $inventory = fractal()->item($data->inventory)->transformWith(new InventoryTransformer())->toArray();
        $field = [
            'id' => $data->udid,
            'inventoryId' => $data->inventoryId,
            'isAdded'=>$data->isAdded,
            'status'=>$data->isActive,
            'issueDate'=>strtotime($data->createdAt),
            'setupDate'=>strtotime($data->updatedAt)
        ];
        return array_merge($inventory['data'],$field);
    }
}
