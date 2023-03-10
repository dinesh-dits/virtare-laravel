<?php

namespace App\Transformers\PatientNew;

use League\Fractal\TransformerAbstract;

class PatientInventoryNewTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [];

    protected $availableIncludes = [];

    public function transform($data)
    {
        return [
            'id' => $data->udid,
            'deviceTypeId' =>(!empty($data->inventory))? (!empty($data->inventory->device))?$data->inventory->device->id:'':'',
            'deviceType' => (!empty($data->inventory))? (!empty($data->inventory->device))?$data->inventory->device->name:'':'',
            'serialNumber' => ($data->inventory->serialNumber != NULL) ? $data->inventory->serialNumber : $data->inventory->macAddress,
            'networkId' => (!empty($data->inventory->network->id)) ? $data->inventory->network->id : '',
            'network' => (!empty($data->inventory->network->id)) ? $data->inventory->network->name : '',
            'manufactureId' => (!empty(@$data->inventory->manufacture->id)) ? @$data->inventory->manufacture->id : '',
            'manufacture' => (!empty(@$data->inventory->manufacture->id)) ? $data->inventory->manufacture->name : '',
            'isActive' => $data->isActive ? True : False,
        ];
    }
}
