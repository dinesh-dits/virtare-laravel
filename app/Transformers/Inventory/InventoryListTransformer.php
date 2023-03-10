<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;

class InventoryListTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        if (empty($data)) {
            return [];
        }
        if ($data->inventory) {
            return [
                'id' => $data->id,
                'deviceModelId' => $data->deviceModelId,
                'deviceType' => (!empty(@$data->model->deviceType->name)) ? @$data->model->deviceType->name : @$data->deviceType,
                'deviceTypeId' => (!empty(@$data->model->deviceType->id)) ? @$data->model->deviceType->id : @$data->deviceTypeId,
                'modelNumber' => $data->modelNumber ? $data->modelNumber : @$data->model->modelName,
                // 'macAddress' => $data->macAddress,
                'serialNumber' => ($data->serialNumber != NULL) ? $data->serialNumber : $data->macAddress,
                'isActive' => $data->isActive ? True : False,
                'isAvailable' => $data->isAvailable,
                'networkId' => (!empty($data->network->id)) ? $data->network->id : '',
                'network' => (!empty($data->network->id)) ? $data->network->name : '',
                'manufactureId' => (!empty($data->manufacture->id)) ? $data->manufacture->id : '',
                'manufacture' => (!empty($data->manufacture->id)) ? $data->manufacture->name : '',
                'patientId' => (!empty($data->inventory->patient)) ? $data->inventory->patient->udid : '',
                'deviceId' => (!empty($data->device)) ? $data->device->id : '',
                'device' => (!empty($data->device)) ? $data->device->name : '',
                'fullName' => (!empty($data->inventory->patient)) ? str_replace("  ", " ", ucfirst(@$data->inventory->patient->lastName) . ',' . ' ' . ucfirst(@$data->inventory->patient->firstName) . ' ' . ucfirst(@$data->inventory->patient->middleName)) : '',
            ];
        }

        return [
            'id' => $data->id,
            'deviceModelId' => $data->deviceModelId,
            'deviceType' => (!empty(@$data->model->deviceType->name)) ? @$data->model->deviceType->name : @$data->deviceType,
            'deviceTypeId' => (!empty(@$data->model->deviceType->id)) ? @$data->model->deviceType->id : @$data->deviceTypeId,
            'modelNumber' => $data->modelNumber ? $data->modelNumber : @$data->model->modelName,
            'macAddress' => ($data->serialNumber != NULL) ? $data->serialNumber : $data->macAddress,
            //'serialNumber' => $data->serialNumber,
            'serialNumber' => ($data->serialNumber != NULL) ? $data->serialNumber : $data->macAddress,
            'isActive' => $data->isActive ? True : False,
            'isAvailable' => $data->isAvailable,
            'networkId' => (!empty($data->network->id)) ? $data->network->id : '',
            'network' => (!empty($data->network->id)) ? $data->network->name : '',
            'manufactureId' => (!empty($data->manufacture->id)) ? $data->manufacture->id : '',
            'manufacture' => (!empty($data->manufacture->id)) ? $data->manufacture->name : '',
            'deviceId' => (!empty($data->device)) ? $data->device->id : '',
            'device' => (!empty($data->device)) ? $data->device->name : '',
        ];
    }
}
