<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Transformers\Vital\VitalFieldTransformer;

class InventoryTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        if (empty($data)) {
            return [];
        }
        return [
            'id' => $data->id,
            'deviceTypeId' => $data->model->deviceType->id,
            'deviceModelId' => $data->deviceModelId,
            'serialNumber' => ($data->serialNumber != NULL) ? $data->serialNumber : $data->macAddress,
            'deviceType' => (!empty($data->model->deviceType->name)) ? $data->model->deviceType->name : $data->deviceType,
            'modelNumber' => $data->modelNumber ? $data->modelNumber : $data->model->modelName,
            'macAddress' => ($data->serialNumber != NULL) ? $data->serialNumber : $data->macAddress,
            'networkId' => (!empty(@$data->network->id)) ? @$data->network->id : '',
            'network' => (!empty(@$data->network->id)) ? @$data->network->network : '',
            'manufactureId' => (!empty(@$data->manufacture->id)) ? @$data->manufacture->id : '',
            'manufacture' => (!empty(@$data->manufacture->id)) ? $data->manufacture->name : '',
            'isActive' => $data->isActive ? True : False,
            'vitalField' => $data->model->deviceType ?  fractal()->collection($data->model->deviceType->vitalFieldType)->transformWith(new VitalFieldTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
        ];
    }
}
